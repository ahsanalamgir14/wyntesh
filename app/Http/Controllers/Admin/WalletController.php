<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\Withdrawal;
use App\Models\Admin\WithdrawalRequest;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\Setting;
use App\Models\User\User;
use Validator;
use JWTAuth;
use Carbon\Carbon;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function withdrawalRequests(Request $request)
    {
        
       
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $status=$request->status;
        $date_range=$request->date_range;

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

        if(!$search && !$status && !$date_range){           
            $WithdrawalRequests=WithdrawalRequest::select();           
            $WithdrawalRequests=$WithdrawalRequests->with('member:id,user_id','member.kyc','approver');
            $WithdrawalRequests=$WithdrawalRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $WithdrawalRequests=WithdrawalRequest::select();
           
            $WithdrawalRequests=$WithdrawalRequests->where(function ($query)use($search) {
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($date_range){
                $WithdrawalRequests=$WithdrawalRequests->whereDate('created_at','>=', $date_range[0]);
                $WithdrawalRequests=$WithdrawalRequests->whereDate('created_at','<=', $date_range[1]);
            }

            if($status){
                $WithdrawalRequests=$WithdrawalRequests->where('request_status',$status);
            }

            $WithdrawalRequests=$WithdrawalRequests->with('member:id,user_id','member.kyc','approver');
            $WithdrawalRequests=$WithdrawalRequests->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pin requests retrieved.",'data'=>$WithdrawalRequests);
        return response()->json($response, 200);
    }

    public function approveWithdrawal(Request $request){
        $user=JWTAuth::user();

        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'tds_percentage' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'tds_amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'final_amount' => "required|regex:/^\d+(\.\d{1,2})?$/"
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $id=$request->id;

        $WithdrawalRequest=WithdrawalRequest::find($id);

        if(!$WithdrawalRequest){
            $response = array('status' => false,'message'=>'Withdrawal request not found','data' => array());
            return response()->json($response, 404);
        }

        $balance=$WithdrawalRequest->member->wallet_balance;        
        $WithdrawalRequest->member->save();
        $WithdrawalRequest->request_status="Approved";
        $WithdrawalRequest->note=$request->note;
        $WithdrawalRequest->approved_by=$user->id;
        $WithdrawalRequest->save();

        $Withdrawal=new Withdrawal;
        $Withdrawal->member_id=$WithdrawalRequest->member_id;
        $Withdrawal->amount=$request->amount;
        $Withdrawal->tds_percentage=$request->tds_percentage;
        $Withdrawal->tds_amount=$request->tds_amount;
        $Withdrawal->net_amount=$request->final_amount;
        $Withdrawal->withdrawal_request_id=$WithdrawalRequest->id;
        $Withdrawal->payment_made_at=$request->payment_made_at;
        $Withdrawal->payment_status=$request->payment_status;
        $Withdrawal->note=$request->note;
        $Withdrawal->transaction_by=$user->id;
        $Withdrawal->save();

        $TransactionType=TransactionType::where('name','Withdrawal')->first();
        $tran_type=2;
        if($TransactionType){
            $tran_type=$TransactionType->id;
        }

        $WalletTransaction=new WalletTransaction;
        $WalletTransaction->member_id=$WithdrawalRequest->member_id;
        $WalletTransaction->amount=$request->final_amount;
        $WalletTransaction->balance=$WithdrawalRequest->member->wallet_balance;
        $WalletTransaction->transaction_type_id=$tran_type;
        $WalletTransaction->note=$request->note;
        $WalletTransaction->save();

        $response = array('status' => true,'message'=>'Withdrawal request approved successfully.');
        return response()->json($response, 200);

    }

    public function getWithdrawals(Request $request)
    {
        $User=JWTAuth::user();

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;


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

        if(!$search && !$date_range ){           
            $Withdrawals=Withdrawal::select();           
            $Withdrawals=$Withdrawals->with('member','transaction_by');
            $Withdrawals=$Withdrawals->orderBy('id',$sort)->paginate($limit);
        }else{
            $Withdrawals=Withdrawal::select();
            
            if($search){
                $Withdrawals=$Withdrawals->where(function ($query)use($search) {
                    $query=$query->orWhere('payment_status',$search);

                    $query=$query->orWhereHas('member.user',function($q)use($search){
                        $q->where('username','like','%'.$search.'%');
                    });
                });    
            }

            if($date_range){
                $Withdrawals=$Withdrawals->whereDate('payment_made_at','>=', $date_range[0]);
                $Withdrawals=$Withdrawals->whereDate('payment_made_at','<=', $date_range[1]);
            }

            $Withdrawals=$Withdrawals->with('member','transaction_by');
            $Withdrawals=$Withdrawals->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Withdrawal requests retrieved.",'data'=>$Withdrawals);
        return response()->json($response, 200);
    }

    public function getWalletTransactions(Request $request)
    {
        $User=JWTAuth::user();

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $transaction_type=$request->transaction_type;
        $transfered_from=$request->transfered_from;
        $transfered_to=$request->transfered_to;
        $transaction_by=$request->transaction_by;
        $member_id=$request->member_id;

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

        if(!$date_range && !$transaction_type && !$transfered_from && !$transfered_to && !$member_id && !$transaction_by){

            $WalletTransactions=WalletTransaction::select();           
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }else{
            $WalletTransactions=WalletTransaction::select();
           
            if($date_range){
                $WalletTransactions=$WalletTransactions->whereDate('created_at','>=', $date_range[0]);
                $WalletTransactions=$WalletTransactions->whereDate('created_at','<=', $date_range[1]);
            }

            if($member_id){
                $WalletTransactions=$WalletTransactions->whereHas('member.user',function($q)use($member_id){
                    $q->where('username','like','%'.$member_id.'%');
                });               
            }

            if($transaction_type){
                $WalletTransactions=$WalletTransactions->where('transaction_type_id',$transaction_type);                
            }

            if($transfered_from){
                $WalletTransactions=$WalletTransactions->whereHas('transfered_from_user',function($q)use($transfered_from){
                    $q->where('username','like','%'.$transfered_from.'%');
                });               
            }

            if($transfered_to){
                $WalletTransactions=$WalletTransactions->whereHas('transfered_to_user',function($q)use($transfered_to){
                    $q->where('username','like','%'.$transfered_to.'%');
                });
            }

            if($transaction_by){
                $WalletTransactions=$WalletTransactions->whereHas('transaction_by_user',function($q)use($transaction_by){
                    $q->where('username','like','%'.$transaction_by.'%');
                });
            }

            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Wallet transaction retrieved.",'data'=>$WalletTransactions);
        return response()->json($response, 200);
    }

    public function getWalletTransfers(Request $request)
    {
        $User=JWTAuth::user();
       
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $transfered_from=$request->transfered_from;
        $transfered_to=$request->transfered_to;
        $transaction_by=$request->transaction_by;
        
        $member_id=$request->member_id;

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

        if(!$date_range &&  !$transfered_to && !$transfered_from && !$transaction_by){

            $WalletTransactions=WalletTransaction::select();           
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            
            $WalletTransactions=$WalletTransactions->whereHas('transaction',function($q){
                $q->where('name','Balance Transfer');
            });
            
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }else{
            $WalletTransactions=WalletTransaction::select();
           
            if($date_range){
                $WalletTransactions=$WalletTransactions->whereDate('created_at','>=', $date_range[0]);
                $WalletTransactions=$WalletTransactions->whereDate('created_at','<=', $date_range[1]);
            }

            if($member_id){
                $WalletTransactions=$WalletTransactions->whereHas('member.user',function($q)use($member_id){
                    $q->where('username','like','%'.$member_id.'%');
                });               
            }


            if($transfered_from){
                $WalletTransactions=$WalletTransactions->whereHas('transfered_from_user',function($q)use($transfered_from){
                    $q->where('username','like','%'.$transfered_from.'%');
                });               
            }

            if($transfered_to){
                $WalletTransactions=$WalletTransactions->whereHas('transfered_to_user',function($q)use($transfered_to){
                    $q->where('username','like','%'.$transfered_to.'%');
                });
            }

            $WalletTransactions=$WalletTransactions->whereHas('transaction',function($q){
                $q->where('name','Balance Transfer');
            });

            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Wallet transfers retrieved.",'data'=>$WalletTransactions);
        return response()->json($response, 200);
    }

     public function createBalanceTransfer(Request $request){
        $user=JWTAuth::user();

        $transfered_from_user=User::where('username',$request->transfer_from)->first();
        $transfered_to_user=User::where('username',$request->transfer_to)->first();

        if(!$transfered_from_user){
            $response = array('status' => false,'message'=>'Transfer from member not found.');
            return response()->json($response, 404);
        }

        if(!$transfered_to_user){
            $response = array('status' => false,'message'=>'Transfer to member not found.');
            return response()->json($response, 404);
        }

        $balance=floatval($transfered_from_user->member->wallet_balance);
        $amount=floatval($request->amount);
      
        if($balance < $amount){
            $response = array('status' => false,'message'=>'Member does not have enough balance to transfer');
            return response()->json($response, 400);
        }

        $TransactionType=TransactionType::where('name','Balance Transfer')->first();

        if($TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$transfered_from_user->member->id;
            $WalletTransaction->balance=$balance-$amount;
            $WalletTransaction->amount=$amount;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transfered_from=$transfered_from_user->id;
            $WalletTransaction->transfered_to=$transfered_to_user->id;
            $WalletTransaction->transaction_by=$user->id;
            $WalletTransaction->note=$request->note;
            $WalletTransaction->save();

            $final_balance=$balance-$amount;
            $transfered_from_user->member->wallet_balance=$final_balance;
            $transfered_from_user->member->save();

            $response = array('status' => true,'message'=>'Balance transfered successfully.');
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Invalid transaction type, contact admin.');
            return response()->json($response, 404);
        }

    }
    
    public function rejectWithdrawalRequest(Request $request)
    {
        $WithdrawalRequest= WithdrawalRequest::find($request->id);
        
         if($WithdrawalRequest){
            $amount=$WithdrawalRequest->amount;
            $WithdrawalRequest->member->wallet_balance+=$amount;
            $WithdrawalRequest->member->save();
            $WithdrawalRequest->request_status='Rejected';
            $WithdrawalRequest->note=$request->note;
            $WithdrawalRequest->save(); 
            $response = array('status' => true,'message'=>'Withdrawal request rejected.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Withdrawal request not found','data' => array());
            return response()->json($response, 404);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $WithdrawalRequest= WithdrawalRequest::where('request_status','Pending')->find($id);
        
         if($WithdrawalRequest){
            $WithdrawalRequest->delete(); 
            $response = array('status' => true,'message'=>'Withdrawal request successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Withdrawal request not found','data' => array());
            return response()->json($response, 404);
        }

    }

    
}
