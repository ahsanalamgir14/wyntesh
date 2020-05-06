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

   

    // public function getPinTransferLog(Request $request)
    // {
        
    //     $page=$request->page;
    //     $limit=$request->limit;
    //     $sort=$request->sort;
    //     $search=$request->search;
    //     $transfer_from=$request->transfer_from;
    //     $transfer_to=$request->transfer_to;

    //     if(!$page){
    //         $page=1;
    //     }

    //     if(!$limit){
    //         $limit=1000;
    //     }

    //     if ($sort=='+id'){
    //         $sort = 'asc';
    //     }else{
    //         $sort = 'desc';
    //     }

    //     if(!$search &&  !$transfer_from && !$transfer_to){           
    //         $PinTransferLogs=PinTransferLog::select();
            
    //         $PinTransferLogs=$PinTransferLogs->with('transfered_from:id,username','transfered_to:id,username','transfered_by:id,username','pin:id,pin_number');
    //         $PinTransferLogs=$PinTransferLogs->orderBy('id',$sort)->paginate($limit);
    //     }else{
    //         $PinTransferLogs=PinTransferLog::select();
            
    //         $PinTransferLogs=$PinTransferLogs->where(function ($query)use($search) {               
    //             $query=$query->orWhereHas('pin',function($q)use($search){
    //                 $q->where('pin_number','like','%'.$search.'%');
    //             });
    //         });
            
           
    //         if($transfer_from){
    //             $User=User::where('username',$transfer_from)->first();
    //             $PinTransferLogs=$PinTransferLogs->where('transfered_from',$User->id);    
    //         }

    //         if($transfer_to){
    //             $User=User::where('username',$transfer_to)->first();
    //             $PinTransferLogs=$PinTransferLogs->where('transfered_to',$User->id);    
    //         }

    //         $PinTransferLogs=$PinTransferLogs->with('transfered_from:id,username','transfered_to:id,username','transfered_by:id,username','pin:id,pin_number');
    //         $PinTransferLogs=$PinTransferLogs->orderBy('id',$sort)->paginate($limit);
    //     }
    //    $response = array('status' => true,'message'=>"Pin Transfer Logs retrieved.",'data'=>$PinTransferLogs);
    //         return response()->json($response, 200);
    // }

   
    // public function transferPinsToMember(Request $request)
    // {
    //     $user_id=JWTAuth::user()->id;
        
    //     $validate = Validator::make($request->all(), [
    //         'member_id' => 'required|integer',
    //     ]);

    //     $Member=User::where('username',$request->member_id)->first();

    //     if(!$Member){
    //          $response = array('status' => false,'message'=>'Member not found');
    //         return response()->json($response, 404);
    //     }

    //     if($validate->fails()){
    //         $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
    //         return response()->json($response, 400);
    //     }

    //     foreach ($request->pins as $pin_id) {
    //         $Pin=Pin::find($pin_id);
    //         if($Pin){
                
    //             $transfered_from='';
    //             if($Pin->owned_by){
    //                 $Member=Member::find($Pin->owned_by);
    //                 if($Member->id){
    //                     $transfered_from=$Member->id;
    //                 }else{
    //                     $transfered_from=$user_id;
    //                 }
    //             }
    //             else
    //             {
    //                 $transfered_from=$user_id;
    //             }

    //             $PinTransferLog=new PinTransferLog;
    //             $PinTransferLog->pin_id=$pin_id;
    //             $PinTransferLog->transfered_from=$transfered_from;
    //             $PinTransferLog->transfered_to=$Member->id;
    //             $PinTransferLog->transfered_by=$user_id;
    //             $PinTransferLog->note=$request->note;
    //             $PinTransferLog->save();

    //             $Pin->owned_by=$Member->member->id;
    //             $Pin->save();
    //         }
    //     }
       
    //     $response = array('status' => true,'message'=>'Pins transferred successfully.');  
    //     return response()->json($response, 200);
    // }

    
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
