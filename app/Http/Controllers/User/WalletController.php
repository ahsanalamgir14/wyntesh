<?php

namespace App\Http\Controllers\User;

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
        $User=JWTAuth::user();
        $balance=$User->member->wallet_balance;
        $kyc_status=$User->member->kyc->is_verified;

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
            $WithdrawalRequests=$WithdrawalRequests->with('member','approver');
            $WithdrawalRequests=$WithdrawalRequests->where('member_id',$User->member->id);
            $WithdrawalRequests=$WithdrawalRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $WithdrawalRequests=WithdrawalRequest::select();
           

            if($date_range){
                $WithdrawalRequests=$WithdrawalRequests->whereDate('created_at','>=', $date_range[0]);
                $WithdrawalRequests=$WithdrawalRequests->whereDate('created_at','<=', $date_range[1]);
            }

            if($status){
                $WithdrawalRequests=$WithdrawalRequests->where('request_status',$status);
            }

            $WithdrawalRequests=$WithdrawalRequests->where('member_id',$User->member->id);
            $WithdrawalRequests=$WithdrawalRequests->with('member','approver');
            $WithdrawalRequests=$WithdrawalRequests->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Withdrawal requests retrieved.",'data'=>$WithdrawalRequests,'balance'=>$balance,'kyc_status'=>$kyc_status);
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

        if(!$search && !$date_range){           
            $Withdrawals=Withdrawal::select();           
            $Withdrawals=$Withdrawals->with('member','transaction_by');
            $Withdrawals=$Withdrawals->where('member_id',$User->member->id);
            $Withdrawals=$Withdrawals->orderBy('id',$sort)->paginate($limit);
        }else{
            $Withdrawals=Withdrawal::select();
           
            if($search){
                $Withdrawals=$Withdrawals->where(function ($query)use($search) {               
                    $query=$query->orwhere('payment_status',$search);
                });
            }
            
            if($date_range){
                $Withdrawals=$Withdrawals->whereDate('payment_made_at','>=', $date_range[0]);
                $Withdrawals=$Withdrawals->whereDate('payment_made_at','<=', $date_range[1]);
            }

            $Withdrawals=$Withdrawals->where('member_id',$User->member->id);
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

        if(!$date_range && !$transaction_type && !$transfered_from && !$transfered_to){

            $WalletTransactions=WalletTransaction::select();           
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->where('member_id',$User->member->id);
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }else{
            $WalletTransactions=WalletTransaction::select();
           
            if($date_range){
                $WalletTransactions=$WalletTransactions->whereDate('created_at','>=', $date_range[0]);
                $WalletTransactions=$WalletTransactions->whereDate('created_at','<=', $date_range[1]);
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

            $WalletTransactions=$WalletTransactions->where('member_id',$User->member->id);
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Wallet transaction retrieved.",'data'=>$WalletTransactions);
        return response()->json($response, 200);
    }

    public function getWalletTransfers(Request $request)
    {
        $User=JWTAuth::user();
        $balance=$User->member->wallet_balance;
        $kyc_status=$User->member->kyc->is_verified;

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $transfered_to=$request->transfered_to;

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

        if(!$date_range &&  !$transfered_to){

            $WalletTransactions=WalletTransaction::select();           
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            
            $WalletTransactions=$WalletTransactions->whereHas('transaction',function($q){
                $q->where('name','Balance Transfer');
            });
            
            $WalletTransactions=$WalletTransactions->where('member_id',$User->member->id);
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }else{
            $WalletTransactions=WalletTransaction::select();
           
            if($date_range){
                $WalletTransactions=$WalletTransactions->whereDate('created_at','>=', $date_range[0]);
                $WalletTransactions=$WalletTransactions->whereDate('created_at','<=', $date_range[1]);
            }

            if($transfered_to){
                $WalletTransactions=$WalletTransactions->whereHas('transfered_to_user',function($q)use($transfered_to){
                    $q->where('username','like','%'.$transfered_to.'%');
                });
            }

            $WalletTransactions=$WalletTransactions->whereHas('transaction',function($q){
                $q->where('name','Balance Transfer');
            });

            $WalletTransactions=$WalletTransactions->where('member_id',$User->member->id);
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Wallet transfers retrieved.",'data'=>$WalletTransactions,'balance'=>$balance,'kyc_status'=>$kyc_status);
        return response()->json($response, 200);
    }

    public function createWithdrawal(Request $request){
        $user=JWTAuth::user();

        $balance=$user->member->wallet_balance;
        $kyc_status=$user->member->kyc->is_verified;               
        $amount=$request->debit;
      
        if($balance < $amount){
            $response = array('status' => false,'message'=>'You do not have enough balance to withdraw');
            return response()->json($response, 400);
        }

        if(!$kyc_status){
            $response = array('status' => false,'message'=>'Verify your KYC first to withdraw');
            return response()->json($response, 400);
        }

        $WithdrawalRequest=new WithdrawalRequest;
        $WithdrawalRequest->member_id=$user->member->id;
        $WithdrawalRequest->amount=$amount;
        $WithdrawalRequest->request_status='Pending';
        $WithdrawalRequest->note=$request->note;
        $WithdrawalRequest->save();

        $final_balance=$balance-$amount;
        $user->member->wallet_balance=$final_balance;
        $user->member->save();

        $response = array('status' => true,'message'=>'Withdrawal request created successfully.');
        return response()->json($response, 200);

    }

    public function createBalanceTransfer(Request $request){
        $user=JWTAuth::user();

        $balance=$user->member->wallet_balance;
        $kyc_status=$user->member->kyc->is_verified;               
        $amount=$request->amount;

        $member_user=User::where('username',$request->transfer_to)->first();

        if(!$member_user){
            $response = array('status' => false,'message'=>'Transfer to member not found.');
            return response()->json($response, 404);
        }
      
        if($balance < $amount){
            $response = array('status' => false,'message'=>'You do not have enough balance to make transfer.');
            return response()->json($response, 400);
        }

        if(!$kyc_status){
            $response = array('status' => false,'message'=>'Verify your KYC first to make transfer.');
            return response()->json($response, 400);
        }

        $TransactionType=TransactionType::where('name','Balance Transfer')->first();

        if($TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$user->member->id;
            $WalletTransaction->balance=$balance-$amount;
            $WalletTransaction->amount=$amount;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transfered_from=$user->id;
            $WalletTransaction->transfered_to=$member_user->id;
            $WalletTransaction->transaction_by=$user->id;
            $WalletTransaction->note=$request->note;
            $WalletTransaction->save();

            $final_balance=$balance-$amount;
            $user->member->wallet_balance=$final_balance;
            $user->member->save();

            $response = array('status' => true,'message'=>'Balance transfered successfully.');
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Invalid transaction type, contact admin.');
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
        $member_id=JWTAuth::user()->member->id;
        $WithdrawalRequest= WithdrawalRequest::where('member_id',$member_id)->where('id',$id)->first();
        
         if($WithdrawalRequest){
            $amount=$WithdrawalRequest->amount;
            $WithdrawalRequest->member->wallet_balance+=$amount;
            $WithdrawalRequest->member->save();
            $WithdrawalRequest->delete(); 
            $response = array('status' => true,'message'=>'Withdrawal request successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Withdrawal request not found');
            return response()->json($response, 404);
        }

    }

    
}
