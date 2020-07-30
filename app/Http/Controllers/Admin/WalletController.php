<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Superadmin\TransactionType;
use App\Models\Admin\Withdrawal;
use App\Models\Admin\WithdrawalRequest;
use App\Models\Admin\WalletTransaction;
use App\Models\Admin\CreditRequest;
use App\Models\Admin\Setting;
use App\Models\User\User;
use Validator;
use JWTAuth;
use Carbon\Carbon;
use DB;

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
            $response = array('status' => false,'message'=>'Withdrawal request not found');
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
            $withdrawals_total=Withdrawal::select([DB::raw('sum(tds_amount) as tds_amount'),DB::raw('sum(net_amount) as net_amount')])->first();
        }else{
            $Withdrawals=Withdrawal::select();
            $Withdrawals=Withdrawal::select();
            $withdrawals_total=Withdrawal::select([DB::raw('sum(tds_amount) as tds_amount'),DB::raw('sum(net_amount) as net_amount')]);
            if($search){
                $Withdrawals=$Withdrawals->where(function ($query)use($search) {
                    $query=$query->orWhere('payment_status',$search);

                    $query=$query->orWhereHas('member.user',function($q)use($search){
                        $q->where('username','like','%'.$search.'%');
                    });
                });

                $withdrawals_total=$withdrawals_total->where(function ($query)use($search) {
                    $query=$query->orWhereHas('member.user',function($q)use($search){
                        $q->where('username','like','%'.$search.'%');
                    });
                });    
            }

            if($date_range){
                $Withdrawals=$Withdrawals->whereDate('payment_made_at','>=', $date_range[0]);
                $Withdrawals=$Withdrawals->whereDate('payment_made_at','<=', $date_range[1]);
                $withdrawals_total=$withdrawals_total->whereDate('payment_made_at','>=', $date_range[0]);
                $withdrawals_total=$withdrawals_total->whereDate('payment_made_at','<=', $date_range[1]);
            }

            $Withdrawals=$Withdrawals->with('member','transaction_by');
            $withdrawals_total=$withdrawals_total->first();
            $Withdrawals=$Withdrawals->orderBy('id',$sort)->paginate($limit);
        }

        
        $response = array('status' => true,'message'=>"Withdrawal requests retrieved.",'data'=>$Withdrawals,'total'=>$withdrawals_total);
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

        if(!$date_range &&  !$transfered_to && !$transfered_from){

            $WalletTransactions=WalletTransaction::select();           
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            
            $WalletTransactions=$WalletTransactions->whereHas('transaction',function($q){
                $q->whereIn('name',['Balance Transfer','Credit']);
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
                $q->whereIn('name',['Balance Transfer','Credit']);
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

            $transfered_to_user->member->wallet_balance+=$amount;
            $transfered_to_user->member->save();
            

            $response = array('status' => true,'message'=>'Balance transfered successfully.');
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Invalid transaction type, contact admin.');
            return response()->json($response, 404);
        }

    }

    public function creditRequests(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;       
        $payment_mode=$request->payment_mode;
        $status=$request->status;

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

        if(!$search  && !$payment_mode && !$status){           
            $CreditRequests=CreditRequest::select();
            
            $CreditRequests=$CreditRequests->with('payment_mode','bank','member:id,user_id','approver:id,name');
            $CreditRequests=$CreditRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $CreditRequests=CreditRequest::select();
            
            $CreditRequests=$CreditRequests->where(function ($query)use($search) {               
                $query=$query->orWhereHas('payment_mode',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($status){
                $CreditRequests=$CreditRequests->where('status',$status);                
            }

            if($payment_mode){
                $CreditRequests=$CreditRequests->where('payment_mode',$payment_mode);                
            }

            $CreditRequests=$CreditRequests->with('payment_mode','bank','member:id,user_id','approver:id,name');
            $CreditRequests=$CreditRequests->orderBy('id',$sort)->paginate($limit);
        }

        
        $response = array('status' => true,'message'=>"Credit requests retrieved.",'data'=>$CreditRequests);
        return response()->json($response, 200);
    }

    public function approveCreditRequest(Request $request)
    {
        $user=JWTAuth::user();
        
        $validate = Validator::make($request->all(), [         
            'request_id' => 'required|integer',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $CreditRequest=CreditRequest::find($request->request_id);
        $CreditRequest->status='Approved';
        $CreditRequest->note=$request->note;
        $CreditRequest->save();

        $balance=floatval($CreditRequest->member->wallet_balance);
        $amount=floatval($CreditRequest->amount);

        $CreditRequest->member->wallet_balance+=$amount;
        $CreditRequest->member->save();

        $TransactionType=TransactionType::where('name','Credit')->first();
        
        $WalletTransaction=new WalletTransaction;
        $WalletTransaction->member_id=$CreditRequest->member->id;
        $WalletTransaction->transfered_to=$CreditRequest->member->user->id;
        $WalletTransaction->balance=$balance+$amount;
        $WalletTransaction->amount=$amount;
        $WalletTransaction->transaction_type_id=$TransactionType->id;
        $WalletTransaction->transaction_by=$user->id;
        $WalletTransaction->note=$request->note;
        $WalletTransaction->save();

        $response = array('status' => true,'message'=>'Wallet Credit requests approved successfully.');  
        return response()->json($response, 200);
    }
    
    public function rejectCreditRequest(Request $request){
        $CreditRequest= CreditRequest::find($request->id);         
        
        if($CreditRequest){
            $CreditRequest->status='Rejected';
            $CreditRequest->note=$request->note;
            $CreditRequest->save(); 
            $response = array('status' => true,'message'=>'Wallet credit request rejected.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Wallet credit request not found');
            return response()->json($response, 404);
        }
    }

    public function getDebitTransactions(Request $request)
    {
        $User=JWTAuth::user();

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $transfered_from=$request->transfered_from;
        $TransactionType=TransactionType::where('name','Debit (Admin)')->first();

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

        if(!$date_range && !$transfered_from ){

            $WalletTransactions=WalletTransaction::select();           
            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->where('transaction_type_id',$TransactionType->id);
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }else{
            $WalletTransactions=WalletTransaction::select();
            
            if($date_range){
                $WalletTransactions=$WalletTransactions->whereDate('created_at','>=', $date_range[0]);
                $WalletTransactions=$WalletTransactions->whereDate('created_at','<=', $date_range[1]);
            }

            $WalletTransactions=$WalletTransactions->where('transaction_type_id',$TransactionType->id);

            if($transfered_from){
                $WalletTransactions=$WalletTransactions->whereHas('transfered_from_user',function($q)use($transfered_from){
                    $q->where('username','like','%'.$transfered_from.'%');
                });               
            }

            $WalletTransactions=$WalletTransactions->with('transaction_by_user','transfered_from_user','transfered_to_user','transaction');
            $WalletTransactions=$WalletTransactions->orderBy('id',$sort)->paginate($limit);
        }

        
        $response = array('status' => true,'message'=>"Wallet transaction retrieved.",'data'=>$WalletTransactions);
        return response()->json($response, 200);
    }

    public function addBalance(Request $request){
        $user=JWTAuth::user();

        $Member=User::where('username',$request->member_id)->first();

        $transfered_from_user=$user;
        $transfered_to_user=$Member;
        
        if(!$transfered_to_user){
            $response = array('status' => false,'message'=>'Member not found.');
            return response()->json($response, 404);
        }

        $balance=floatval($transfered_to_user->member->wallet_balance);
        $amount=$request->amount;
        $TransactionType=TransactionType::where('name','Credit')->first();
        
        if($TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$transfered_to_user->member->id;
            $WalletTransaction->balance=$balance+$amount;
            $WalletTransaction->amount=$amount;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transfered_from=$transfered_from_user->id;
            $WalletTransaction->transfered_to=$transfered_to_user->id;
            $WalletTransaction->transaction_by=$user->id;
            $WalletTransaction->note=$request->note;
            $WalletTransaction->save();

            $final_balance=$balance+$amount;
            $transfered_to_user->member->wallet_balance=$final_balance;
            $transfered_to_user->member->save();

            $response = array('status' => true,'message'=>'Balance added successfully.');
            return response()->json($response, 200);

        }else{
            $response = array('status' => false,'message'=>'Invalid transaction type, contact admin.');
            return response()->json($response, 404);
        }

    }

    public function debitBalance(Request $request){
        $user=JWTAuth::user();

        $Member=User::where('username',$request->member_id)->first();

        $transfered_from_user=$user;
        $debited_from=$Member;
        
        if(!$debited_from){
            $response = array('status' => false,'message'=>'Member not found.');
            return response()->json($response, 404);
        }

        $balance=floatval($debited_from->member->wallet_balance);
        $amount=$request->amount;
        $TransactionType=TransactionType::where('name','Debit (Admin)')->first();
        
        if($balance<$amount){
            $response = array('status' => false,'message'=>'Member does not have enough balance.');
            return response()->json($response, 404);
        }

        if($TransactionType){
            $WalletTransaction=new WalletTransaction;
            $WalletTransaction->member_id=$Member->id;
            $WalletTransaction->balance=$balance-$amount;
            $WalletTransaction->amount=$amount;
            $WalletTransaction->transaction_type_id=$TransactionType->id;
            $WalletTransaction->transfered_from=$debited_from->id;
            $WalletTransaction->transaction_by=$user->id;
            $WalletTransaction->note=$request->note;
            $WalletTransaction->save();

            $final_balance=$balance-$amount;
            $debited_from->member->wallet_balance=$final_balance;
            $debited_from->member->save();

            $response = array('status' => true,'message'=>'Balance debited successfully.');
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
            $response = array('status' => false,'message'=>'Withdrawal request not found');
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
            $response = array('status' => false,'message'=>'Withdrawal request not found');
            return response()->json($response, 404);
        }

    }

    
}
