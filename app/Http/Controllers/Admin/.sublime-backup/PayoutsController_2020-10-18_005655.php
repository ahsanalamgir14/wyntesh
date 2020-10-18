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
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\Reward;
use App\Models\Admin\Setting;
use App\Models\Admin\CompanySetting;
use JWTAuth;
use App\Models\Admin\WallOfWyntash;
use App\Models\User\Order;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\IncomeWalletTransactions;
use Carbon\Carbon;
use DB;
 
class PayoutsController extends Controller
{    

    
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
        //$user=JWTAuth::user();
        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $MemberPayout=MemberPayout::select();
        $MemberPayout=$MemberPayout->where('id',$id);
        $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.kyc')->first();

        // dd($MemberPayout->payout_id);
        $MemberPayoutIncome=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->with('income')->get();
        $user_details=array('name' => $MemberPayout->member->user->name,'username'=>$MemberPayout->member->user->username,'profile_picture'=>$MemberPayout->member->user->profile_picture,'rank'=>$MemberPayout->member->rank->name );

        // dd($MemberPayout->payout);
        $affiliter = AffiliateBonus::addSelect([\DB::raw('sum(amount) as final_amount')])
                        ->where("member_id",$MemberPayout->member->id)
                        ->whereDate('created_at','>=', $MemberPayout->payout->sales_start_date)
                        ->whereDate('created_at','<=', $MemberPayout->payout->sales_end_date)
                        ->groupBy('member_id')
                        ->first();
// dd($affiliter);
        $affiliter = $affiliter?$affiliter->final_amount:"0";
        $response = array('status' => true,'message'=>"Member Payout retrieved.",'payout'=>$MemberPayout,'incomes'=>$MemberPayoutIncome,'company_details'=>$settings,'user'=>$user_details,'affiliter'=>$affiliter);
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

        if(!$search && !$income_id && !$month){
            $MemberPayoutIncome=MemberPayoutIncome::select();
            
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout','member.user:id,username,name')->orderBy('id',$sort)->paginate($limit); 
        }else{
            $MemberPayoutIncome=MemberPayoutIncome::select();
            
            $MemberPayoutIncome=$MemberPayoutIncome->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($month){
                $MemberPayoutIncome=$MemberPayoutIncome->whereHas('payout',function($q)use($month){
                    $month=$month.'-01';
                    $date=Carbon::parse($month);
                    $q->whereMonth('sales_start_date',$date->month);
                    $q->whereYear('sales_start_date',$date->year);
                });
            }

            if($income_id){
                $MemberPayoutIncome=$MemberPayoutIncome->where('income_id',$income_id);
            }
            
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout','member.user:id,username,name')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Payout Incomes retrieved.",'data'=>$MemberPayoutIncome);
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
        $Payout->total_payout=0;
        $Payout->save();

        foreach ($request->incomes as $income_id) {
            $PayoutIncome=new PayoutIncome;
            $PayoutIncome->payout_id=$Payout->id;
            $PayoutIncome->income_id=$income_id;
            $PayoutIncome->payout_amount=0;
            $PayoutIncome->save();
        }

        event(new GeneratePayoutEvent($Payout));

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

        if(!$search && !$month){
            $MemberPayout=MemberPayout::select();
            
            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.user:id,username,name')->where('total_payout','>',0)->orderBy('id',$sort)->paginate($limit);
        }else{
            $MemberPayout=MemberPayout::select();
            $MemberPayout=$MemberPayout->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($month){
                $MemberPayout=$MemberPayout->whereHas('payout',function($q)use($month){
                    $month=$month.'-01';
                    $date=Carbon::parse($month);
                    $q->whereMonth('sales_start_date',$date->month);
                    $q->whereYear('sales_start_date',$date->year);
                });
            }

            $MemberPayout=$MemberPayout->where('total_payout','>',0)->with('payout:id,sales_start_date,sales_end_date','member.user:id,username,name')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$MemberPayout);
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


        if(!$search){
            $memberWallet=Member::select()->with('user')->where('wallet_balance','>',0)->orderBy('wallet_balance',$sort)->paginate($limit);
        }else{
            $memberWallet=Member::select();
            if($search){

                // $memberWallet=$memberWallet->where('id',$search);
                $memberWallet=$memberWallet->where(function ($query)use($search) {              
                    $query=$query->WhereHas('user',function($q)use($search){
                        $q->where('username','like','%'.$search.'%');
                    });
                });


            }


            $memberWallet=$memberWallet->with('user')->where('wallet_balance','>',0)->orderBy('wallet_balance',$sort)->paginate($limit);
        }


   
        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$memberWallet);
        return response()->json($response, 200);
    }
    public function getMemberTDS(Request $request)
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
            $ofset = $page*$limit;
            $MemberPayout=  DB::select(DB::raw(" SELECT *, SUM(amt) tds FROM ( SELECT ab.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, ab.member_id, SUM(tds_amount) AS amt FROM `affiliate_bonus` AS ab RIGHT JOIN `members` AS m ON m.id = ab.member_id RIGHT JOIN `users` AS u ON u.id = m.user_id RIGHT JOIN `kyc` AS kyc ON kyc.member_id = m.id RIGHT JOIN kyc AS k ON k.member_id = m.id  GROUP BY member_id, YEAR(ab.created_at), MONTH(ab.created_at) UNION SELECT tp.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, tp.member_id, SUM(tds) AS amt FROM `member_payouts` AS tp LEFT JOIN `members` AS m ON m.id = tp.member_id LEFT JOIN `users` AS u ON u.id = m.user_id LEFT JOIN `kyc` AS kyc ON kyc.member_id = m.id   GROUP BY tp.member_id, YEAR(tp.created_at), MONTH(tp.created_at) ) tmp GROUP BY tmp.member_id,YEAR(tmp.created_at), MONTH(tmp.created_at) HAVING tds > 0 LIMIT  $page ,$limit") );
        }else{
            $ofset = $page*$limit;
            $affiliate = "";
            $memberPayout  =""; 
            if($search && $month){
                $users = User::where('username',$search)->with('member')->first();
                $member_id= $users->member->id;
                $month=$request->month;
                $year = date('Y',strtotime($month));
                $month = date('m',strtotime($month));

                $affiliate      = "WHERE ab.member_id = $member_id and YEAR(ab.created_at) = $year AND MONTH(ab.created_at) = $month";
                $memberPayout   = "WHERE tp.member_id = $member_id and YEAR(tp.created_at) = $year AND MONTH(tp.created_at) = $month";
            }
            else if($search){
                $users = User::where('username',$search)->with('member')->first();
                $member_id= $users->member->id;
                $affiliate      = "WHERE ab.member_id = $member_id ";
                $memberPayout   = "WHERE tp.member_id = $member_id ";
            }
            else if($month){
                $year = date('Y',strtotime($month));
                $month = date('m',strtotime($month));
                $affiliate      = "WHERE YEAR(ab.created_at) = $year AND MONTH(ab.created_at) = $month";
                $memberPayout   = "WHERE YEAR(tp.created_at) = $year AND MONTH(tp.created_at) = $month";
            }

            $MemberPayout=  DB::select(DB::raw(" SELECT *, SUM(amt) tds FROM ( SELECT ab.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, ab.member_id, SUM(tds_amount) AS amt FROM `affiliate_bonus` AS ab RIGHT JOIN `members` AS m ON m.id = ab.member_id RIGHT JOIN `users` AS u ON u.id = m.user_id RIGHT JOIN `kyc` AS kyc ON kyc.member_id = m.id RIGHT JOIN kyc AS k ON k.member_id = m.id  $affiliate GROUP BY member_id, YEAR(ab.created_at), MONTH(ab.created_at) UNION SELECT tp.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, tp.member_id, SUM(tds) AS amt FROM `member_payouts` AS tp LEFT JOIN `members` AS m ON m.id = tp.member_id LEFT JOIN `users` AS u ON u.id = m.user_id LEFT JOIN `kyc` AS kyc ON kyc.member_id = m.id  $memberPayout GROUP BY tp.member_id, YEAR(tp.created_at), MONTH(tp.created_at) ) tmp GROUP BY tmp.member_id,YEAR(tmp.created_at), MONTH(tmp.created_at) HAVING tds > 0 LIMIT  $limit OFFSET $ofset ") );
        }
        
        $affiliate = "";
        $memberPayout  =""; 
        if($search && $month){
            $users = User::where('username',$search)->with('member')->first();
            $member_id= $users->member->id;
            $month=$request->month;
            $year = date('Y',strtotime($month));
            $month = date('m',strtotime($month));
            $affiliate      = "WHERE ab.member_id = $member_id and YEAR(ab.created_at) = $year AND MONTH(ab.created_at) = $month";
            $memberPayout   = "WHERE tp.member_id = $member_id and YEAR(tp.created_at) = $year AND MONTH(tp.created_at) = $month";
        }
        else if($search){
            $users = User::where('username',$search)->with('member')->first();
            $member_id= $users->member->id;
            $affiliate      = "WHERE ab.member_id = $member_id ";
            $memberPayout   = "WHERE tp.member_id = $member_id ";
        }
        else if($month){
            $month=$request->month;
            $year = date('Y',strtotime($month));
            $month = date('m',strtotime($month));
            $affiliate      = "WHERE YEAR(ab.created_at) = $year AND MONTH(ab.created_at) = $month";
            $memberPayout   = "WHERE YEAR(tp.created_at) = $year AND MONTH(tp.created_at) = $month";
        }


        $sum=  DB::select(DB::raw(" SELECT *, SUM(amt) tds FROM ( SELECT ab.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, ab.member_id, SUM(tds_amount) AS amt FROM `affiliate_bonus` AS ab RIGHT JOIN `members` AS m ON m.id = ab.member_id RIGHT JOIN `users` AS u ON u.id = m.user_id RIGHT JOIN `kyc` AS kyc ON kyc.member_id = m.id    RIGHT JOIN kyc AS k ON k.member_id = m.id $affiliate UNION SELECT tp.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, tp.member_id, SUM(tds) AS amt FROM `member_payouts` AS tp LEFT JOIN `members` AS m ON m.id = tp.member_id LEFT JOIN `users` AS u ON u.id = m.user_id LEFT JOIN `kyc` AS kyc ON kyc.member_id = m.id $memberPayout ) tmp") );
        $sum = $sum[0]->tds;

        $totalCount=   DB::select(DB::raw(" SELECT *, SUM(amt) tds FROM ( SELECT ab.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, ab.member_id, SUM(tds_amount) AS amt FROM `affiliate_bonus` AS ab RIGHT JOIN `members` AS m ON m.id = ab.member_id RIGHT JOIN `users` AS u ON u.id = m.user_id RIGHT JOIN `kyc` AS kyc ON kyc.member_id = m.id  RIGHT JOIN kyc AS k ON k.member_id = m.id  $affiliate GROUP BY member_id, YEAR(ab.created_at), MONTH(ab.created_at) UNION SELECT tp.created_at, u.name, u.username, u.dob, kyc.pan, kyc.city, tp.member_id, SUM(tds) AS amt FROM `member_payouts` AS tp LEFT JOIN `members` AS m ON m.id = tp.member_id LEFT JOIN `users` AS u ON u.id = m.user_id LEFT JOIN `kyc` AS kyc ON kyc.member_id = m.id  $memberPayout GROUP BY tp.member_id, YEAR(tp.created_at), MONTH(tp.created_at) ) tmp GROUP BY  tmp.member_id,YEAR(tmp.created_at), MONTH(tmp.created_at) HAVING tds > 0  ") );

        $totalCount = count($totalCount);

        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$MemberPayout,'sum'=>$sum,'total'=>$totalCount);
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
