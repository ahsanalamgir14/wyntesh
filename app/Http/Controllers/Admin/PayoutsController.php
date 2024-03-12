<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Payout;
use App\Models\Admin\Member;
use App\Models\User\User;
use App\Models\Admin\PayoutType;
use App\Models\Admin\PayoutIncome;
use App\Events\GeneratePayoutEvent;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\MemberMonthlyLegPv;
use App\Models\Admin\MemberCarryForwardPv;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\Reward;
use App\Models\Admin\Setting;
use App\Models\Admin\CompanySetting;
use JWTAuth;
use App\Models\Admin\WallOfWyntash;
use App\Models\User\Order;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\MatchingPoint;
use App\Models\Admin\IncomeWalletTransactions;
use Carbon\Carbon;
use DB;
use App\Jobs\ProcessTestMatching;

class PayoutsController extends Controller
{    

    public function generateMatchingPoints(Request $request){

        $date_range=$request->date_range;
        DB::statement('TRUNCATE matching_points');
        ProcessTestMatching::dispatch($date_range[0],$date_range[1]);

        $response = array('status' => true,'message'=>"Matching process is started in background, refresh after a while.");
        return response()->json($response, 200);

        // $Members=Member::whereHas('user',function($q){
        //     $q->where('is_active',1);
        //     $q->where('is_blocked',0);
        // })->get();


        // $total_sales=Order::whereNotIn('delivery_status',['Order Cancelled','Order Returned'])->whereDate('created_at','<=',$date_range[1])
        //                 ->whereDate('created_at','>=',$date_range[0])
        //                 ->sum('pv');

        // foreach ($Members as $Member) {
        //     $this->calculateMemberMatchedBV($Member,$request,$total_sales);
        // }
    }

    public function calculateMemberMatchedBV($Member,$request,$total_sales){
        
        $matched_bv=0;
        $carry_forward=0;
        $carry_forward_position=0;
        $leg_1_pv=0;
        $leg_2_pv=0;
        $date_range=$request->date_range;

        //Counting Carry forward and Matched points of Member Legs.

        //Getting Member Legs in decenting based on current PV
        $legs=0;
        $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                    ->whereDate('created_at','>=',$date_range[0])
                    ->whereDate('created_at','<=',$date_range[1])
                    ->where('member_id',$Member->id)
                    ->orderBy('totalPv','desc')
                    ->groupBy('position')
                    ->get()->pluck('totalPv','position')->toArray();
        
        $MatchingPoint=new MatchingPoint;
        $MatchingPoint->member_id=$Member->id;    
        $MatchingPoint->total_sales=$total_sales;    
            
        $MatchingPoint->save();

        $last_carry_forward=MemberCarryForwardPv::where('member_id',$Member->id)->orderBy('payout_id','desc')->first();

        if($last_carry_forward){

            $MatchingPoint->previous_carry_forward=$last_carry_forward->pv;
            $MatchingPoint->previous_carry_forward_position=$last_carry_forward->position;
            $MatchingPoint->save();

            if(count($legs)){
                $exsting_pv=intval(isset($legs[$last_carry_forward->position])?$legs[$last_carry_forward->position]:0);
                $legs[$last_carry_forward->position]=$exsting_pv+$last_carry_forward->pv;
            }
        }


            if(isset($legs[1])){
                $MatchingPoint->leg_1=$legs[1];
            }

            if(isset($legs[2])){
                $MatchingPoint->leg_2=$legs[2];
            }

            if(isset($legs[3])){
                $MatchingPoint->leg_3=$legs[3];
            }

            if(isset($legs[4])){
                $MatchingPoint->leg_4=$legs[4];
            }

        arsort($legs);

        $index = 0;
        foreach ($legs as $position => $pv) {
            if($index==0){
                $leg_1_pv=$pv;
                $carry_forward_position=$position;

                // If only 1 leg then no matching bonus, carry forward all current pv
                if(count($legs)==1){
                    $carry_forward=$pv;                        
                }
                $index++;
                continue;
            }

            if($index==1){
                $leg_2_pv=$pv;
                // Count carry forward
                $carry_forward=$leg_1_pv-$leg_2_pv;
            }
                   
            // Add current pv to matched_bv of all legs except strong one.
            $matched_bv+=$pv;
            $index++;
        }

        if($matched_bv<=0){
            $matched_bv=0;
        }

        $MatchingPoint->matched=floatval($matched_bv/24);
        $MatchingPoint->carry_forward=$carry_forward;
        $MatchingPoint->carry_forward_position=$carry_forward_position;
        $MatchingPoint->save();
    }

    public function getMatchingPoints(Request $request){
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }
    
        $matchingPoints=MatchingPoint::select();
        
        if($search){
            $matchingPoints=$matchingPoints->whereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
            });
        }

        $matchingPoints=$matchingPoints->where('matched','>',0)->with('member.user')->orderBy('id',$sort)->paginate($limit);

        $MatchingPoint=MatchingPoint::first();
        $total_bv=$MatchingPoint?$MatchingPoint->total_sales:0;
        $total_matching_points=MatchingPoint::sum('matched');
 
        $response = array('status' => true,'message'=>"Data retrieved.",'data'=>$matchingPoints,'sum'=>array('total_matched'=>$total_matching_points,'total_bv'=>$total_bv));
        return response()->json($response, 200);
    }

    public function wallOfWyntash(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }
    
        if(!$search){

            $results = WallOfWyntash::orderBy('id',$sort)->with('user')->where('total_amount','>=',10000)->paginate($limit);
           
        }else{

            $results=WallOfWyntash::select()->with('user')->where('total_amount','>=',10000);
             if($search){
                $results->where('username',$search);
            }
            $results=$results->orderBy('id',$sort)->paginate($limit);
 
        }
        $response = array('status' => true,'message'=>"Data retrieved.",'data'=>$results);
        return response()->json($response, 200);
    }
    public function rewards(Request $request)
    {

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        // $month=$request->month;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){
            $reward = Reward::with('member.user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $reward=Reward::with('member.user')->select();
            if($search){
                $reward->where('name',$search);
            }
            $reward=$reward->orderBy('id',$sort)->paginate($limit);
        }

        $tds_percentage=CompanySetting::getValue('tds_percentage');
   
        $response = array('status' => true,'message'=>"Reward retrieved.",'data'=>$reward,'tds'=>$tds_percentage);
        return response()->json($response, 200);
    }

    public function generateHolding(Request $request) {
        // dd($request->member_id);
        $memberHolding=MemberIncomeHolding::where('id',$request->id)->first();
        if($request->debit<=$memberHolding->amount){
            $memberHolding->amount -= $request->debit;
            $memberHolding->save();

            $response = array('status' => true,'message'=>'Amount deducted Successfully.','data' => $memberHolding);
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Holding amount is not enough','data' => $memberHolding);
            return response()->json($response, 404);  
        }
    }
    public function memberCheck($code) {
        $User=User::where('username',$code)->with('member')->role('user')->first();

        if($User){
            $response = array('status' => true,'message'=>'Sponsor recieved.','data' => $User);
            return response()->json($response, 200);  
        }else{
            $response = array('status' => false,'message'=>'Member not found','data' => $User);
            return response()->json($response, 404);  
        }
        
    }

    public function getPayouts(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $month=$request->month;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search && !$month){
            $Payout = Payout::with('payout_type','incomes.income')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Payout=Payout::select();
            if($month){
                $month=$month.'-01';
                $date=Carbon::parse($month);
                $Payout->whereMonth('sales_start_date',$date->month);
                $Payout->whereYear('sales_start_date',$date->year);
            }

            $Payout=$Payout->with('payout_type','incomes')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Payouts retrieved.",'data'=>$Payout);
        return response()->json($response, 200);
    }

    public function getMemberPayout($id)
    {
        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $MemberPayout=MemberPayout::select();
        $MemberPayout=$MemberPayout->where('id',$id);
        $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.kyc')->first();

        $MemberPayoutIncome=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->with('income')->get();
        
        $user_details=array('name' => $MemberPayout->member->user->name,'username'=>$MemberPayout->member->user->username,'profile_picture'=>$MemberPayout->member->user->profile_picture,'rank'=>$MemberPayout->member->rank->name );

        $response = array('status' => true,'message'=>"Member Payout retrieved.",'payout'=>$MemberPayout,'incomes'=>$MemberPayoutIncome,'company_details'=>$settings,'user'=>$user_details);
        return response()->json($response, 200);
    }


    public function getMemberPayoutIncomes(Request $request)
    {   
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $month=$request->month;
        $income_id=$request->income_id;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        $MemberPayoutIncome=MemberPayoutIncome::select();

        $MemberPayoutTotal=MemberPayoutIncome::select([DB::raw('sum(payout_amount) as total_payout_amount'),DB::raw('sum(tds) as total_tds'),DB::raw('sum(admin_fee) as total_admin_fee'),DB::raw('sum(net_payable_amount) as total_net_payable_amount')]);
        
        if($search){
            $MemberPayoutIncome=$MemberPayoutIncome->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            $MemberPayoutTotal=$MemberPayoutTotal->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });


        }

        if($month){
            $MemberPayoutIncome=$MemberPayoutIncome->whereHas('payout',function($q)use($month){
                $month=$month.'-01';
                $date=Carbon::parse($month);
                $q->whereMonth('sales_start_date',$date->month);
                $q->whereYear('sales_start_date',$date->year);
            });

            $MemberPayoutTotal=$MemberPayoutTotal->whereHas('payout',function($q)use($month){
                $month=$month.'-01';
                $date=Carbon::parse($month);
                $q->whereMonth('sales_start_date',$date->month);
                $q->whereYear('sales_start_date',$date->year);
            });
        }

        if($income_id){
            $MemberPayoutIncome=$MemberPayoutIncome->where('income_id',$income_id);
            $MemberPayoutTotal=$MemberPayoutTotal->where('income_id',$income_id);
        }
        
        $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout','member.user:id,username,name')->orderBy('id',$sort)->paginate($limit);

        $MemberPayoutTotal=$MemberPayoutTotal->first();
   
        $response = array('status' => true,'message'=>"Payout Incomes retrieved.",'data'=>$MemberPayoutIncome,'sum'=>$MemberPayoutTotal);
        return response()->json($response, 200);
    }

  
    public function generateManualPayout(Request $request){

        $PayoutType=PayoutType::where('name','Monthly')->first();

        // $request->date_range    = ['2020-07-20','2020-07-30'];        
        // $request->incomes       = ['3','4'];        
        
        $validate = Validator::make($request->all(), [
            'date_range' => 'required',
            'incomes' => 'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $date_range=$request->date_range;
        
        $Payout=new Payout;
        $Payout->payout_type_id=$PayoutType->id;
        $Payout->is_run_by_system=0;
        $Payout->sales_start_date=$request->date_range[0];
        $Payout->sales_end_date=$request->date_range[1];
        $Payout->sales_bv=0;
        $Payout->sales_amount=0;
        $Payout->payout_amount=0;
        $Payout->save();

        foreach ($request->incomes as $income_id) {
            $PayoutIncome=new PayoutIncome;
            $PayoutIncome->payout_id=$Payout->id;
            $PayoutIncome->income_id=$income_id;
            $PayoutIncome->payout_amount=0;
            $PayoutIncome->save();
        }

        event(new GeneratePayoutEvent($Payout));
        // GeneratePayoutEvent::dispatch($Payout);

        $response = array('status' => true,'message'=>'Payout Generation added to queue.');
        return response()->json($response, 200);
    }

    public function AddReward(Request $request){
            
        $user=User::where('username',$request->member_id)->with('member')->role('user')->first();
        if(!$user){  
            $response = array('status' => false,'message'=>'Member not found','data' => $user);
            return response()->json($response, 404);  
        }
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required',
            'tds_percent' => 'required',
            'tds_amount' => 'required',
            'member_id' => 'required',
            'final_amount' => 'required',
        ]);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $oldBalance = $user->member->income_wallet_balance;

        $user->member->income_wallet_balance += $request->final_amount;
        $user->member->save();

        $TransactionType=TransactionType::where('name','Credit')->first();

        $IncomeWalletTransactions=new IncomeWalletTransactions;
        $IncomeWalletTransactions->member_id= $user->member->id;
        $IncomeWalletTransactions->amount= $request->final_amount;
        $IncomeWalletTransactions->balance= $oldBalance + $request->final_amount;
        $IncomeWalletTransactions->transaction_type_id=$TransactionType->id;
        $IncomeWalletTransactions->transfered_to=$user->id;
        $IncomeWalletTransactions->transaction_by=JWTAuth::user()->id;
        $IncomeWalletTransactions->note='Reward Bonus';
        $IncomeWalletTransactions->save(); 


        $Payout=new Reward;
        $Payout->name           = $request->name;
        $Payout->member_id      = $request->mem_id;
        $Payout->amount         = $request->amount;
        $Payout->tds_percent    = $request->tds_percent;
        $Payout->tds_amount     = $request->tds_amount;
        $Payout->final_amount   = $request->final_amount;
        $Payout->given_at       = date("y-m-d h:i:s");
        $Payout->save();


        $response = array('status' => true,'message'=>'Reward added');
        return response()->json($response, 200);
    }

    public function getPayoutIncomes(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $month=$request->month;
        $income_id=$request->income_id;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search && !$income_id && !$month){
            $PayoutIncome = PayoutIncome::with('income','payout')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $PayoutIncome=PayoutIncome::select();
            
            if($month){
                $PayoutIncome=$PayoutIncome->whereHas('payout',function($q)use($month){
                    $month=$month.'-01';
                    $date=Carbon::parse($month);
                    $q->whereMonth('sales_start_date',$date->month);
                    $q->whereYear('sales_start_date',$date->year);
                });
            }

            if($income_id){
                $PayoutIncome=$PayoutIncome->whereIn('income_id',$income_id);
            }
            
            $PayoutIncome=$PayoutIncome->with('income','payout')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Payout Incomes retrieved.",'data'=>$PayoutIncome);
        return response()->json($response, 200);
    }

    public function getGroupAndMatchingPvs(Request $request)
    {   
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $member_id=$request->member_id;
        $Member='';
        
        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if($member_id){
            $User=User::where('username',$member_id)->first();
            if($User){
                $Member=$User->member->id;    
            }else{
                $response = array('status' => false,'message'=>"Member not found");
                return response()->json($response, 404);
            }
            
        }else{
            $response = array('status' => false,'message'=>"Member Id is required.");
            return response()->json($response, 400);
        }

        $distinct_months=MemberMonthlyLegPv::selectRaw('distinct(DATE_FORMAT(created_at,"%Y-%m")) as month')->orderBy('created_at','desc')->paginate($limit);

        $monthly_pvs=array();
        foreach ($distinct_months as $val) {
            $date=date_create($val->month);
            $month= date_format($date,"m");
            $year= date_format($date,"Y");
            $MemberMonthlyLegPv=MemberMonthlyLegPv::selectRaw('*')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
            ->where('member_id',$Member)->orderBy('position','asc')->get();  
            $monthly_pvs[]=array('month'=>$val->month,'legs'=>$MemberMonthlyLegPv);
        }
        
        $response = array('status' => true,'message'=>"Member Leg Pvs retrieved.",'data'=>$monthly_pvs,'total'=>count($distinct_months));
        return response()->json($response, 200);
    }

    public function getMemberPayouts(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $month=$request->month;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        $MemberPayout=MemberPayout::select();
        $MemberPayoutTotal=MemberPayout::select([DB::raw('sum(payout_amount) as total_payout_amount'),DB::raw('sum(tds) as total_tds'),DB::raw('sum(admin_fee) as total_admin_fee'),DB::raw('sum(net_payable_amount) as total_net_payable_amount')]);

        if($search){
            $MemberPayout=$MemberPayout->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            $MemberPayoutTotal=$MemberPayoutTotal->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });
        }

        if($month){
            $MemberPayout=$MemberPayout->whereHas('payout',function($q)use($month){
                $month=$month.'-01';
                $date=Carbon::parse($month);
                $q->whereMonth('sales_start_date',$date->month);
                $q->whereYear('sales_start_date',$date->year);
            });
            $MemberPayoutTotal=$MemberPayoutTotal->whereHas('payout',function($q)use($month){
                $month=$month.'-01';
                $date=Carbon::parse($month);
                $q->whereMonth('sales_start_date',$date->month);
                $q->whereYear('sales_start_date',$date->year);
            });
        }

        $MemberPayout=$MemberPayout->where('payout_amount','>',0)->with('payout:id,sales_start_date,sales_end_date','member.user:id,username,name')->orderBy('id',$sort)->paginate($limit);

        $MemberPayoutTotal=$MemberPayoutTotal->first();

        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$MemberPayout,'sum'=>$MemberPayoutTotal);
        return response()->json($response, 200);
    }

    public function getMemberTopWallet(Request $request)
    {   
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        $memberWallet=Member::select();

        $total=Member::select([DB::raw('sum(wallet_balance) as total_wallet_balance'),DB::raw('sum(income_wallet_balance) as total_income_wallet_balance'),DB::raw('sum(luxury_wallet_balance) as total_luxury_wallet_balance')]);


        if($search){
            $memberWallet=$memberWallet->where(function ($query)use($search) {
                $query=$query->WhereHas('user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            $total=$total->where(function ($query)use($search) {
                $query=$query->WhereHas('user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });
        }

        $total=$total->first();

        $memberWallet=$memberWallet->with('user')->orderBy('wallet_balance',$sort)->paginate($limit);

        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$memberWallet,'sum'=>$total);
        return response()->json($response, 200);
    }

    public function getMemberTDS(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $month=$request->month?$request->month:date('Y-m-d');

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        $MemberPayout=MemberPayout::select();
        $MemberPayout=$MemberPayout->where('tds','!=',0);
        $total=MemberPayout::select([DB::raw('sum(tds) as total_tds'),DB::raw('sum(payout_amount) as total_payout_amount'),DB::raw('sum(admin_fee) as total_admin_fee'),DB::raw('sum(net_payable_amount) as total_net_payable_amount')]);

        if($search){
            $MemberPayout=$MemberPayout->where(function ($query)use($search) {
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });
            $total=$total->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });
        }
                
        if($month){
            $MemberPayout=$MemberPayout->whereHas('payout',function($q)use($month){
                $month=$month.'-01';
                $date=Carbon::parse($month);
                $q->whereMonth('sales_start_date',$date->month);
                $q->whereYear('sales_start_date',$date->year);
            });
            $total=$total->whereHas('payout',function($q)use($month){
                $date=Carbon::parse($month);
                $q->whereMonth('sales_start_date',$date->month);
                $q->whereYear('sales_start_date',$date->year);
            });
        }
        
        $total=$total->first();
        $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.user:id,username,name','member.kyc')->orderBy('id',$sort)->paginate($limit);

        $response = array('status' => true,'message'=>"Member TDS retrieved.",'data'=>$MemberPayout,'sum'=>$total);
        return response()->json($response, 200);
    }

    public function getMonthlyBusiness(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $month=$request->month?$request->month:'';

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        $Payout=Payout::select([DB::raw('sum(sales_bv) as total_sales_bv'),DB::raw('sum(sales_amount) as total_sales_amount'),DB::raw('sum(sales_gst) as total_sales_gst'),DB::raw('sum(sales_shipping_fee) as total_sales_shipping_fee'),DB::raw('sum(tds) as total_tds'),DB::raw('sum(payout_amount) as total_payout_amount'),DB::raw('sum(net_payable_amount) as total_net_payable_amount'),DB::raw('sales_start_date'),DB::raw("CONCAT_WS('-',MONTH(sales_start_date),YEAR(sales_start_date)) as monthyear")]);

                
        if($month){ 
            $month=$month.'-01';
            $date=Carbon::parse($month);           
            $Payout=$Payout->whereMonth('sales_start_date',$date->month);
            $Payout=$Payout->whereYear('sales_start_date',$date->year);
        }
        
        $Payout=$Payout->groupBy('monthyear')->orderBy('id',$sort)->paginate($limit);

        $response = array('status' => true,'message'=>"Business Overview retrieved.",'data'=>$Payout);
        return response()->json($response, 200);
    }


    public function getMemberIncomeHoldings(Request $request)
    {
        
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=100000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search ){
            $MemberIncomeHolding=MemberIncomeHolding::groupBy('payout_id','member_id')
           ->with('payout','member.user:id,username')->selectRaw('*, sum(amount) as withhold_amount')
           ->where('is_paid',0)->paginate($limit);
        }else{
            $MemberIncomeHolding=MemberIncomeHolding::groupBy('payout_id');
            $MemberIncomeHolding=$MemberIncomeHolding->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });
            $MemberIncomeHolding=$MemberIncomeHolding->with('payout','member.user:id,username')->selectRaw('*, sum(amount) as withhold_amount')
           ->where('is_paid',0)->paginate($limit);
        }

        $response = array('status' => true,'message'=>"Member Income Holding retrieved.",'data'=>$MemberIncomeHolding);
        return response()->json($response, 200);
    }

    public function releaseMemberHoldPayout(Request $request){
        $validate = Validator::make($request->all(), [
            'payout_id' => 'required',
            'member_id'=>'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Member=Member::find($request->member_id);

        if($Member){
            $this->releaseHoldPayout($request->payout_id,$Member->user);
            $response = array('status' => true,'message'=>"Member Income Holding released and credited to member account.");
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>"Member not found.");
            return response()->json($response, 404);
        }
    }    

    public function releaseHoldPayout($payout_id,$user){
        
        $TransactionType=TransactionType::where('name','Credit')->first();

        $MemberIncomeHolding=MemberIncomeHolding::selectRaw('*, sum(amount) as withhold_amount')
       ->where('member_id',$user->member->id)->where('payout_id',$payout_id)->where('is_paid',0)->first();

        if($MemberIncomeHolding->withhold_amount && $TransactionType){
            $IncomeWalletTransactions=new IncomeWalletTransactions;
            $IncomeWalletTransactions->member_id=$user->member->id;
            $IncomeWalletTransactions->amount=$MemberIncomeHolding->withhold_amount;
            $IncomeWalletTransactions->balance=$MemberIncomeHolding->withhold_amount+$user->member->wallet_balance;
            $IncomeWalletTransactions->transaction_type_id=$TransactionType->id;
            $IncomeWalletTransactions->transfered_to=$user->id;
            $IncomeWalletTransactions->note='Withhold Payout';
            $IncomeWalletTransactions->save(); 

            $user->member->income_wallet_balance+=$MemberIncomeHolding->withhold_amount;
            $user->member->save();

            MemberIncomeHolding::where('member_id',$user->member->id)->where('payout_id',$payout_id)->where('is_paid',0)->update(['is_paid'=>1,'paid_at'=>Carbon::now()]);
        }
    }
}
