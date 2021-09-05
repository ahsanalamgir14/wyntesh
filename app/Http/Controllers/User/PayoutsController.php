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

use App\Models\Admin\Reward;
use App\Models\Admin\MemberCarryForwardPv;
use App\Models\User\Order;
use App\Models\User\User;
use App\Models\Admin\Sale;
use App\Models\Admin\CompanySetting;
use App\Models\Admin\Setting;
use JWTAuth;
use DB;
use Carbon\Carbon;

class PayoutsController extends Controller
{    

    
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

    public function rewards(Request $request)
    {
        $user=JWTAuth::user();
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
            $reward = Reward::with('member.user')->where('member_id',$user->member->id)->orderBy('id',$sort)->paginate($limit);    
        }else{
            $reward=Reward::with('member.user')->select();
            if($search){
                $reward->where('name',$search);
            }
            $reward=$reward->orderBy('id',$sort)->where('member_id',$user->member->id)->paginate($limit);
        }

        $tds_percentage=CompanySetting::getValue('tds_percentage');
   
        $response = array('status' => true,'message'=>"Reward retrieved.",'data'=>$reward,'tds'=>$tds_percentage);
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
            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.kyc')->orderBy('id',$sort)->paginate($limit);
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
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout:id,sales_start_date,sales_end_date,payout_type_id')->orderBy('id',$sort)->paginate($limit); 
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
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout:id,sales_start_date,sales_end_date,payout_type_id')->orderBy('id',$sort)->paginate($limit);
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
        ->with('payout:id,sales_start_date,sales_end_date,payout_type_id')->selectRaw('*, sum(amount) as withhold_amount')
        ->where('member_id',$user->member->id)->where('is_paid',0)->paginate($limit);

        $response = array('status' => true,'message'=>"Member Income Holding retrieved.",'data'=>$MemberIncomeHolding);
        return response()->json($response, 200);
    }

    public function getIncomeHoldingPayouts(Request $request)
    {
        $user=JWTAuth::user();
        

        $MemberIncomeHolding=MemberIncomeHolding::groupBy('payout_id')
        ->with('payout:id,sales_start_date,sales_end_date,payout_type_id')->selectRaw('*, sum(amount) as withhold_amount')
        ->where('member_id',$user->member->id)->where('is_paid',0)->get();

        $response = array('status' => true,'message'=>"Member Income Holding Payouts retrieved.",'data'=>$MemberIncomeHolding);
        return response()->json($response, 200);
    }

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

        $last_date=$lastPayout->sales_end_date->addDays(1)->format('Y-m-d');
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
            $monthly_pvs['position']    = $carryForwordPv?$carryForwordPv->position:"";
            $monthly_pvs['pv']          = $carryForwordPv?$carryForwordPv->pv:"";
            $monthly_pvs['legs']        = $legsArray;
        }

      
        $response = array('status' => true,'message'=>"Member Leg Pvs retrieved.",'data'=>$monthly_pvs,'total'=>1);
        return response()->json($response, 200);
    }

    public function getDailyBVReport(Request $request)
    {   
        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $bv_date=$request->bv_date;

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

        $MembersLegPv=MembersLegPv::select();
        $MembersLegPv=$MembersLegPv->where('member_id',$user->member->id);
        $MembersLegPv=$MembersLegPv->addSelect(DB::raw('DATE(created_at) as date'),DB::raw('sum(pv) as total_pv'));
        
        if($bv_date){
            $MembersLegPv=$MembersLegPv->whereDate('created_at',$bv_date);
        }else{            
            $MembersLegPv=$MembersLegPv->whereDate('created_at',Carbon::now());
        }

        $MembersLegPv=$MembersLegPv->groupBy('position')->get();

        
        $legPositions=array('A'=>0,'B'=>0,'C'=>0,'D'=>0);
        $legArray=array(1=>'A',2=>'B',3=>'C',4=>'D');


        foreach ($MembersLegPv as $legPv) {
            $legPositions[$legArray[$legPv->position]]=$legPv->total_pv;
        }
   
        $response = array('status' => true,'message'=>"Daily BV retrieved.",'data'=>array($legPositions));
        return response()->json($response, 200);
    }

    public function getMemberPayoutReport($id)
    {   
        $user=JWTAuth::user();

        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $MemberPayout=MemberPayout::select();
        $MemberPayout=$MemberPayout->where('id',$id)->where('member_id',$user->member->id);
        $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date,payout_type_id','member.kyc')->first();

        if(!$MemberPayout){
             $response = array('status' => false,'message'=>'Member payout not found');
            return response()->json($response, 404);  
        }

        if($MemberPayout->payout->payout_type->name=="Weekly"){
             $MemberPayoutIncome= MemberPayoutIncome::addSelect(['*', \DB::raw('sum(payout_amount) as total_payout_amount')])
                    ->whereDate('created_at','<=',$MemberPayout->payout->sales_end_date)
                    ->whereDate('created_at','>=',$MemberPayout->payout->sales_start_date)
                    ->where('payout_id',$MemberPayout->payout_id)
                    ->where('member_id',$MemberPayout->member->id)
                    ->with('income')
                    ->groupBy('income_id')
                    ->get();
        }else{
             $MemberPayoutIncome= MemberPayoutIncome::addSelect(['*', \DB::raw('sum(payout_amount) as total_payout_amount')])
                    ->whereMonth('created_at',$MemberPayout->payout->sales_end_date->month)
                    ->whereYear('created_at',$MemberPayout->payout->sales_end_date->year)
                    ->where('member_id',$MemberPayout->member->id)
                    ->with('income')
                    ->groupBy('income_id')
                    ->get();
        }


        if($MemberPayout->payout->payout_type->name=="Weekly"){
             $payout= MemberPayout::addSelect(['*', \DB::raw('sum(payout_amount) as total_payout_amount'), \DB::raw('sum(tds) as total_tds'), \DB::raw('sum(net_payable_amount) as total_net_payable_amount')])
                    ->whereDate('created_at','<=',$MemberPayout->payout->sales_end_date)
                    ->whereDate('created_at','>=',$MemberPayout->payout->sales_start_date)
                    ->where('payout_id',$MemberPayout->payout_id)
                    ->where('member_id',$MemberPayout->member->id)
                    ->with('member.user','payout:id,sales_start_date,sales_end_date,payout_type_id','payout.payout_type')
                    ->first();
        }else{
            $payout= MemberPayout::addSelect(['*', \DB::raw('sum(payout_amount) as total_payout_amount'), \DB::raw('sum(tds) as total_tds'), \DB::raw('sum(net_payable_amount) as total_net_payable_amount')])
                    ->whereMonth('created_at',$MemberPayout->payout->sales_end_date->month)
                    ->whereYear('created_at',$MemberPayout->payout->sales_end_date->year)
                    ->where('member_id',$MemberPayout->member->id)
                    ->with('member.user')
                    ->first();

        }

        $user_details=array('name' => $MemberPayout->member->user->name,'username'=>$MemberPayout->member->user->username,'profile_picture'=>$MemberPayout->member->user->profile_picture,'rank'=>$MemberPayout->member->rank->name,'contact'=> $MemberPayout->member->user->contact);

        $response = array('status' => true,'message'=>"Member Payout retrieved.",'payout'=>$payout,'incomes'=>$MemberPayoutIncome,'company_details'=>$settings,'user'=>$user_details);
        return response()->json($response, 200);
    }


}
