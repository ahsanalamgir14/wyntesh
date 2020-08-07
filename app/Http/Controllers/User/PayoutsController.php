<?php

namespace App\Http\Controllers\User;

use App\Events\UpdateGroupPVEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\MemberIncomeHolding;
use App\Models\Admin\MembersLegPv;
use App\Models\Admin\AffiliateBonus;
use App\Models\Admin\Payout;
use App\Models\Admin\MemberCarryForwardPv;
use App\Models\User\Order;
use App\Models\User\User;
use App\Models\Admin\Sale;
use JWTAuth;
use DB;
use Carbon\Carbon;

class PayoutsController extends Controller
{    

    public function payout_pro(){

        $Order  = Order::with('user')->whereNotIn('delivery_status',['Order Cancelled','Order Returned','Order Created'])->get();
      
        foreach ($Order as $key => $value) {
            // dd($value);
            $userdata = User::where("id",$value->user_id)->with('member')->first();

            // Entry in sale table
            $sale = new Sale();
            $sale->member_id                        = $userdata->member->id; 
            $sale->order_id                         = $value->id; 
            $sale->pv                               = $value->pv; 
            $sale->final_amount_company             = $value->amount; 
            $sale->created_at                       = $value->created_at; 
            $sale->save();

            // Update memebr info pvs info
            $userdata->member->current_personal_pv  += $value->pv;
            $userdata->member->total_personal_pv    += $value->pv;
            $userdata->member->save();

            event(new UpdateGroupPVEvent($value,$value->user,'add'));

        }
    }


    public function myAffiliateBonus(Request $request){
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

        $AffiliateBonus=AffiliateBonus::select();
        
        $AffiliateBonus=$AffiliateBonus->where('member_id',$user->member->id);
        
        if($search){
            $AffiliateBonus=$AffiliateBonus->whereHas('order',function($query)use($search){
                $query->where('order_no','like','%'.$search.'%');
            });
        }
        
        $AffiliateBonus=$AffiliateBonus->with('order.user')->orderBy('id',$sort)->paginate($limit);

        $response = array('status' => true,'message'=>"Affiliate Bonus retrieved.",'data'=>$AffiliateBonus);
        return response()->json($response, 200);
    }

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
            $MemberPayoutIncome=$MemberPayoutIncome->where('member_id',$user->member->id);
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout')->orderBy('id',$sort)->paginate($limit); 
        }else{
            $MemberPayoutIncome=MemberPayoutIncome::select();
            
            if($month){
                $MemberPayoutIncome=$MemberPayoutIncome->whereHas('payout',function($q)use($month){
                    $month=$month.'-01';
                    $date=Carbon::parse($month);
                    $q->whereMonth('sales_start_date',$date->month);
                    $q->whereYear('sales_start_date',$date->year);
                });
            }

            if($income_id){
                $MemberPayoutIncome=$MemberPayoutIncome->whereIn('income_id',$income_id);
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

    /*public function getGroupAndMatchingPvs(Request $request)
    {   
        // matching pv;

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
        $lastPayout =Payout::orderBy('id','desc')->first();

        $allPayouts =Payout::whereYear('sales_start_date', '=', date("Y"))
                    ->whereMonth('sales_start_date', '=', date("m"))
                    // ->orderBy('id','desc')
                    ->get()->pluck('id');
        dd($allPayouts);

        $carryForwordPv = MemberCarryForwardPv::where('member_id',$user->member->id)->where('payout_id',$lastPayout->id)->orderBy('created_at','desc')->first();

        $distinct_months =MembersLegPv::selectRaw('distinct(DATE_FORMAT(created_at,"%Y-%m")) as month')->orderBy('created_at','desc')->get();
        // dd($distinct_months);
        $monthly_pvs=array();
        $monthly_pvs['dates']   = $lastPayout->sales_start_date.'-'.$lastPayout->sales_end_date;
        $monthly_pvs['position'] = $carryForwordPv->position;
        $monthly_pvs['pv']      = $carryForwordPv->pv;
        $monthly_pvs['data']    = $carryForwordPv;

        // foreach ($distinct_months as $val) {
        //     $date=date_create($val->month);
        //     $month= date_format($date,"m");
        //     $year= date_format($date,"Y");
        //     $MembersLegPv=MembersLegPv::selectRaw('*')
        //     ->whereYear('created_at', '=', $year)
        //     ->whereMonth('created_at', '=', $month)
        //     ->where('member_id',$user->member->id)->orderBy('position','asc')->get();  
        //     $monthly_pvs[]=array('month'=>$val->month,'legs'=>$MembersLegPv);
        // }

// dd($monthly_pvs);

        // dd($distinct_months);
        $response = array('status' => true,'message'=>"Member Leg Pvs retrieved.",'data'=>$monthly_pvs,'total'=>count($distinct_months));
        return response()->json($response, 200);
    }
*/
    public function getGroupAndMatchingPvs(Request $request)
    {   
        // matching pv;

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
        $lastPayout =Payout::orderBy('id','desc')->first();

        $last_date=Carbon::createFromDate($lastPayout->sales_end_date)->addDays(1)->format('Y-m-d');
        $allPayouts =Payout::whereYear('sales_start_date', '=', date("Y"))
                    ->whereMonth('sales_start_date', '=', date("m"))
                    ->get();
        
        $monthly_pvs = array();
      
        if($lastPayout){

            $carryForwordPv = MemberCarryForwardPv::where('member_id',$user->member->id)->where('payout_id',$lastPayout->id)->orderBy('created_at','desc')->first();

            $legs= MembersLegPv::addSelect(['*', \DB::raw('sum(pv) as totalPv')])
                        ->whereDate('created_at','>=',$last_date)
                        ->where('member_id',$user->member->id)
                        ->orderBy('totalPv','desc')
                        ->groupBy('position')
                        ->get();
            $legsArray[]=$legs;
            $monthly_pvs['dates']       = date('Y-m-d',strtotime($lastPayout->sales_start_date)).'  |  '.date('Y-m-d',strtotime($lastPayout->sales_end_date));
            $monthly_pvs['position']    = $carryForwordPv->position;
            $monthly_pvs['pv']          = $carryForwordPv->pv;
            $monthly_pvs['legs']        = $legsArray;
        }

        
      

      
        $response = array('status' => true,'message'=>"Member Leg Pvs retrieved.",'data'=>$monthly_pvs,'total'=>1);
        return response()->json($response, 200);
    }



}
