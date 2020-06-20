<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Payout;
use App\Models\Admin\Member;
use App\Models\Admin\PayoutType;
use App\Models\Admin\PayoutIncome;
use App\Events\GenerateMonthlyPayoutEvent;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\MemberIncomeHolding;

use App\Models\User\Order;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\WalletTransaction;
use Carbon\Carbon;

class PayoutsController extends Controller
{    

    
    public function getPayouts(Request $request)
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
            $Payout = Payout::with('payout_type','incomes.income')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Payout=Payout::select();
            
            $Payout=$Payout->with('payout_type','incomes')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Payouts retrieved.",'data'=>$Payout);
        return response()->json($response, 200);
    }
  
    public function generateManualPayout(Request $request){
        
        $PayoutType=PayoutType::where('name','Manual')->first();


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

        event(new GenerateMonthlyPayoutEvent($Payout));

        $response = array('status' => true,'message'=>'Payout Generation added to queue.');
        return response()->json($response, 200);
    }

    public function getPayoutIncomes(Request $request)
    {
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
            $PayoutIncome = PayoutIncome::with('income','payout')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $PayoutIncome=PayoutIncome::select();
            
            if($date_range){
                $PayoutIncome=$PayoutIncome->whereDate('created_at','>=', $date_range[0]);
                $PayoutIncome=$PayoutIncome->whereDate('created_at','<=', $date_range[1]);
            }

            if($income_id){
                $PayoutIncome=$PayoutIncome->where('income_id',$income_id);
            }
            
            $PayoutIncome=$PayoutIncome->with('income','payout')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Payout Incomes retrieved.",'data'=>$PayoutIncome);
        return response()->json($response, 200);
    }

    public function getMemberPayouts(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;

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

        if(!$search && !$date_range){
            $MemberPayout=MemberPayout::select();
            
            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.user:id,username')->orderBy('id',$sort)->paginate($limit);
        }else{
            $MemberPayout=MemberPayout::select();
            $MemberPayout=$MemberPayout->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($date_range){
                $MemberPayout=$MemberPayout->whereDate('created_at','>=', $date_range[0]);
                $MemberPayout=$MemberPayout->whereDate('created_at','<=', $date_range[1]);
            }

            $MemberPayout=$MemberPayout->with('payout:id,sales_start_date,sales_end_date','member.user:id,username')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"MemberPayout Types retrieved.",'data'=>$MemberPayout);
        return response()->json($response, 200);
    }

    public function getMemberPayoutIncomes(Request $request)
    {   
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
            
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout','member.user:id,username')->orderBy('id',$sort)->paginate($limit); 
        }else{
            $MemberPayoutIncome=MemberPayoutIncome::select();
            
            $MemberPayoutIncome=$MemberPayoutIncome->where(function ($query)use($search) {              
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($date_range){
                $MemberPayoutIncome=$MemberPayoutIncome->whereDate('created_at','>=', $date_range[0]);
                $MemberPayoutIncome=$MemberPayoutIncome->whereDate('created_at','<=', $date_range[1]);
            }

            if($income_id){
                $MemberPayoutIncome=$MemberPayoutIncome->where('income_id',$income_id);
            }
            
            $MemberPayoutIncome=$MemberPayoutIncome->with('income','payout','member.user:id,username')->orderBy('id',$sort)->paginate($limit);
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
            $limit=1;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search ){
            $MemberIncomeHolding=MemberIncomeHolding::groupBy('payout_id')
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
