<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\MemberMonthlyLegPv;
use JWTAuth;

class PayoutsController extends Controller
{    

    
    public function getPayouts(Request $request)
    {
        $user=JWTAuth::user();
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
            $MemberPayout=MemberPayout::select();
            $MemberPayout=$MemberPayout->where('member_id',$user->member->id);
            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date')->orderBy('id',$sort)->paginate($limit);
        }else{
            $MemberPayout=MemberPayout::select();
            $MemberPayout=$MemberPayout->where('member_id',$user->member->id);
            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$MemberPayout);
        return response()->json($response, 200);
    }

    public function getPayoutIncomes(Request $request)
    {   
        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
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

        if(!$search && !$income_id && !$date_range){
            $MemberPayoutIncome=MemberPayoutIncome::select();
            $MemberPayoutIncome=$MemberPayoutIncome->where('member_id',$user->member->id);
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout')->orderBy('id',$sort)->paginate($limit); 
        }else{
            $MemberPayoutIncome=MemberPayoutIncome::select();
            
            if($date_range){
                $MemberPayoutIncome=$MemberPayoutIncome->whereDate('created_at','>=', $date_range[0]);
                $MemberPayoutIncome=$MemberPayoutIncome->whereDate('created_at','<=', $date_range[1]);
            }

            if($income_id){
                $MemberPayoutIncome=$MemberPayoutIncome->where('income_id',$income_id);
            }
            $MemberPayoutIncome=$MemberPayoutIncome->where('member_id',$user->member->id);
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Payout Incomes retrieved.",'data'=>$MemberPayoutIncome);
        return response()->json($response, 200);
    }

    public function getIncomeHoldings(Request $request)
    {
        $user=JWTAuth::user();
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

        $MemberIncomeHolding=MemberIncomeHolding::groupBy('payout_id')
       ->with('payout')->selectRaw('*, sum(amount) as withhold_amount')
       ->where('member_id',$user->member->id)->where('is_paid',0)->paginate($limit);
   
        $response = array('status' => true,'message'=>"Member Income Holding retrieved.",'data'=>$MemberIncomeHolding);
        return response()->json($response, 200);
    }

    public function getIncomeHoldingPayouts(Request $request)
    {
        $user=JWTAuth::user();
        

        $MemberIncomeHolding=MemberIncomeHolding::groupBy('payout_id')
       ->with('payout')->selectRaw('*, sum(amount) as withhold_amount')
       ->where('member_id',$user->member->id)->where('is_paid',0)->get();
   
        $response = array('status' => true,'message'=>"Member Income Holding Payouts retrieved.",'data'=>$MemberIncomeHolding);
        return response()->json($response, 200);
    }

    public function getGroupAndMatchingPvs(Request $request)
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

        $user=JWTAuth::user();
        $distinct_months=MemberMonthlyLegPv::selectRaw('distinct(DATE_FORMAT(created_at,"%Y-%m")) as month')->orderBy('created_at','desc')->paginate($limit);

        $monthly_pvs=array();
        foreach ($distinct_months as $val) {
            $date=date_create($val->month);
            $month= date_format($date,"m");
            $year= date_format($date,"Y");
            $MemberMonthlyLegPv=MemberMonthlyLegPv::selectRaw('*')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
            ->where('member_id',$user->member->id)->get();  
            $monthly_pvs[]=array('month'=>$val->month,'legs'=>$MemberMonthlyLegPv);
        }

        
   
        $response = array('status' => true,'message'=>"Member Leg Pvs retrieved.",'data'=>$monthly_pvs,'total'=>count($distinct_months));
        return response()->json($response, 200);
    }
  
}
