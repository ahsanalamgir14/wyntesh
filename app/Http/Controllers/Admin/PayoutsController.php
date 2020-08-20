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
use App\Models\Admin\Setting;

use App\Models\User\Order;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\WalletTransaction;
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


        $last = new Carbon('last day of last month');
        $last = $last->startOfMonth()->format('Y-m-d'); 

        $start = new Carbon('first day of last month');
        $start = $start->startOfMonth()->format('Y-m-d H:i:s'); 

        $start = '2020-08-01';
        $last = '2020-08-31';
        $lastdata = 10;
        $limitn  = $page ==1?0:$page*$lastdata;



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
            $results = DB::select( DB::raw("SELECT *,sum(amt) total_amt from (SELECT  ab.created_at , u.name , u.username, u.dob , k.city , ab.member_id,sum(final_amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id right join `users` as u on u.id = m.user_id  right join kyc as k on k.member_id = m.id group by ab.member_id UNION SELECT tp.created_at , u.name ,u.username,u.dob ,k.city,tp.member_id,sum(total_payout) as amt FROM `member_payouts` as tp left join `members` as m on m.id = tp.member_id left join `users` as u on u.id = m.user_id left join kyc as k on k.member_id = m.id group by member_id) tmp where tmp.created_at between '".$start."' and '".$last."'  group by tmp.member_id order by total_amt desc limit $limitn, $lastdata ") );

            $cnt = DB::select( DB::raw("SELECT count(*) as cnt ,sum(amt) total_amt from (SELECT  ab.created_at , u.name , u.username, u.dob , k.city , ab.member_id,sum(final_amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id right join `users` as u on u.id = m.user_id  right join kyc as k on k.member_id = m.id group by ab.member_id UNION SELECT tp.created_at , u.name ,u.username,u.dob ,k.city,tp.member_id,sum(total_payout) as amt FROM `member_payouts` as tp left join `members` as m on m.id = tp.member_id left join `users` as u on u.id = m.user_id left join kyc as k on k.member_id = m.id group by member_id ) tmp where tmp.created_at between '".$start."' and '".$last."'  group by tmp.member_id order by total_amt desc") );


        }else{
            $results = DB::select( DB::raw("SELECT *,sum(amt) total_amt from (SELECT  ab.created_at , u.name , u.username, u.dob , k.city , ab.member_id,sum(final_amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id right join `users` as u on u.id = m.user_id  right join kyc as k on k.member_id = m.id group by ab.member_id UNION SELECT tp.created_at , u.name ,u.username,u.dob ,k.city,tp.member_id,sum(total_payout) as amt FROM `member_payouts` as tp left join `members` as m on m.id = tp.member_id left join `users` as u on u.id = m.user_id left join kyc as k on k.member_id = m.id group by member_id) tmp where tmp.created_at between '".$start."' and '".$last."' and tmp.member_id = $search group by tmp.member_id order by total_amt desc limit $limitn, $lastdata ") );
            $cnt = DB::select( DB::raw("SELECT count(*) as cnt ,sum(amt) total_amt from (SELECT  ab.created_at , u.name , u.username, u.dob , k.city , ab.member_id,sum(final_amount) as amt FROM `affiliate_bonus` as ab right join `members` as m on m.id = ab.member_id right join `users` as u on u.id = m.user_id  right join kyc as k on k.member_id = m.id group by ab.member_id UNION SELECT tp.created_at , u.name ,u.username,u.dob ,k.city,tp.member_id,sum(total_payout) as amt FROM `member_payouts` as tp left join `members` as m on m.id = tp.member_id left join `users` as u on u.id = m.user_id left join kyc as k on k.member_id = m.id group by member_id ) tmp where tmp.created_at between '".$start."' and '".$last."'   and tmp.member_id = $search group by tmp.member_id order by total_amt desc") );
        }



        $response = array('status' => true,'message'=>"Payouts retrieved.",'data'=>$results , 'total'=>count($cnt));
        return response()->json($response, 200);
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
        $MemberPayoutIncome=MemberPayoutIncome::where('payout_id',$MemberPayout->payout_id)->where('member_id',$MemberPayout->member_id)->with('income')->get();
        $user_details=array('name' => $MemberPayout->member->user->name,'username'=>$MemberPayout->member->user->username,'profile_picture'=>$MemberPayout->member->user->profile_picture,'rank'=>$MemberPayout->member->rank->name );
   
        $affiliter = AffiliateBonus::addSelect([\DB::raw('sum(final_amount) as final_amount')])
                        ->where("member_id",$MemberPayout->member->id)
                        ->whereDate('created_at','>=', $MemberPayout->payout->sales_start_date)
                        ->whereDate('created_at','<=', $MemberPayout->payout->sales_end_date)
                        ->groupBy('member_id')
                        ->first();
        $affiliter = $affiliter?$affiliter->final_amount:"0";
        $response = array('status' => true,'message'=>"Member Payout retrieved.",'payout'=>$MemberPayout,'incomes'=>$MemberPayoutIncome,'company_details'=>$settings,'user'=>$user_details,'affiliter'=>$affiliter);
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
            $MemberPayout=MemberPayout::select();
            //$MemberPayout=$MemberPayout->where('total_payout','!=',0);
            
            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.user:id,username,name','member.kyc')->orderBy('id',$sort)->paginate($limit);
            $total=MemberPayout::select([DB::raw('sum(tds) as tds_amount')])->first();
        }else{
            $MemberPayout=MemberPayout::select();
            $total=MemberPayout::select([DB::raw('sum(tds) as tds_amount')]);

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
            //$MemberPayout=$MemberPayout->where('total_payout','!=',0);
            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.user:id,username,name')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$MemberPayout,'sum'=>$total);
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
        
        $TransactionType=TransactionType::where('name','Withhold Payout')->first();

        $MemberIncomeHolding=MemberIncomeHolding::selectRaw('*, sum(amount) as withhold_amount')
       ->where('member_id',$user->member->id)->where('payout_id',$payout_id)->where('is_paid',0)->first();

        if($MemberIncomeHolding->withhold_amount && $TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$user->member->id;
            $WalletTransaction->amount=$MemberIncomeHolding->withhold_amount;
            $WalletTransaction->balance=$MemberIncomeHolding->withhold_amount+$user->member->wallet_balance;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transfered_to=$user->id;
            $WalletTransaction->note='Withhold Payout';
            $WalletTransaction->save(); 

            $user->member->wallet_balance+=$MemberIncomeHolding->withhold_amount;
            $user->member->save();

            MemberIncomeHolding::where('member_id',$user->member->id)->where('payout_id',$payout_id)->where('is_paid',0)->update(['is_paid'=>1,'paid_at'=>Carbon::now()]);
        }
    }
}
