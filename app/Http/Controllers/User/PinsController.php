<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Pin;
use App\Models\User\User;
use App\Models\Admin\PinRequest;
use App\Models\Admin\PinTransferLog;
use Validator;
use JWTAuth;

class PinsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function myPendingPinRequests(Request $request)
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
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){           
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user->member->id);
            $PinRequests=$PinRequests->where('status','Pending');

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user->member->id);
            $PinRequests=$PinRequests->where('status','Pending');

            $PinRequests=$PinRequests->where(function ($query)use($search) {
                $query=$query->orWhereHas('package',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('payment_mode',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
            });

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pin requests retrieved.",'data'=>$PinRequests);
            return response()->json($response, 200);
    }

    public function myApprovedPinRequests(Request $request)
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
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){           
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user->member->id);
            $PinRequests=$PinRequests->where('status','Approved');

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user->member->id);
            $PinRequests=$PinRequests->where('status','Approved');

            $PinRequests=$PinRequests->where(function ($query)use($search) {
                $query=$query->orWhereHas('package',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('payment_mode',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
            });

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pin requests retrieved.",'data'=>$PinRequests);
            return response()->json($response, 200);
    }

    public function myRejectedPinRequests(Request $request)
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
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){           
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user->member->id);
            $PinRequests=$PinRequests->where('status','Rejected');

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user->member->id);
            $PinRequests=$PinRequests->where('status','Rejected');

            $PinRequests=$PinRequests->where(function ($query)use($search) {
                $query=$query->orWhereHas('package',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('payment_mode',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
            });

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pin requests retrieved.",'data'=>$PinRequests);
            return response()->json($response, 200);
    }

    public function getRequestPins(Request $request,$id)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
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

        if(!$search && !$status ){           
            $Pins=Pin::select();
            $Pins=$Pins->where('request_id',$id);

            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }else{
            $Pins=Pin::select();
            $Pins=$Pins->where('request_id',$id);

            $Pins=$Pins->where(function ($query)use($search) {
                $query=$query->orWhere('pin_number',$search);

                $query=$query->orWhereHas('owner.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($status){
                $Pins=$Pins->where('status',$status);                
            }


            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pins retrieved.",'data'=>$Pins);
            return response()->json($response, 200);
    }

    public function getMyPins(Request $request)
    {
        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $status=$request->status;
        $package_id=$request->package_id;

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

        if(!$search && !$status && !$package_id){           
            $Pins=Pin::select();
            $Pins=$Pins->where('owned_by',$user->member->id);

            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }else{
            $Pins=Pin::select();
            $Pins=$Pins->where('owned_by',$user->member->id);

            $Pins=$Pins->where(function ($query)use($search) {
                $query=$query->orWhere('pin_number',$search);

                $query=$query->orWhereHas('owner.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($status){
                $Pins=$Pins->where('status',$status);                
            }

            if($package_id){
                $Pins=$Pins->where('package_id',$package_id);                
            }


            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pins retrieved.",'data'=>$Pins);
            return response()->json($response, 200);
    }

    public function getNotUsedPins(Request $request)
    {
        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $package_id=$request->package_id;

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

        if(!$search &&  !$package_id){           
            $Pins=Pin::select();
            $Pins=$Pins->where('owned_by',$user->member->id);

            $Pins=$Pins->where('status','Not Used');
            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }else{
            $Pins=Pin::select();
            
            if($search){
                $Pins=$Pins->where(function ($query)use($search) {
                    $query=$query->orWhere('pin_number',$search);
                });    
            }
            
            if($package_id){
                $Pins=$Pins->where('package_id',$package_id);
            }

            $Pins=$Pins->where('owned_by',$user->member->id);
            $Pins=$Pins->where('status','Not Used');
            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }
       $response = array('status' => true,'message'=>"Pins retrieved.",'data'=>$Pins);
            return response()->json($response, 200);
    }

    public function transferPinsToMember(Request $request)
    {
        $user=JWTAuth::user();
        
        $validate = Validator::make($request->all(), [
            'member_id' => 'required|integer',
        ]);

        $Member=User::where('username',$request->member_id)->first();

        if(!$Member){
            $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);
        }

        if($Member->username==$user->username){
            $response = array('status' => false,'message'=>'You can not transfer to yourself.');
            return response()->json($response, 400);
        }

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        foreach ($request->pins as $pin_id) {
            $Pin=Pin::find($pin_id);
            if($Pin){
                if($Pin->owned_by==$user->member->id){

                    $PinTransferLog=new PinTransferLog;
                    $PinTransferLog->pin_id=$pin_id;
                    $PinTransferLog->transfered_from=$user->id;
                    $PinTransferLog->transfered_to=$Member->id;
                    $PinTransferLog->transfered_by=$user->id;
                    $PinTransferLog->note=$request->note;
                    $PinTransferLog->save();

                    $Pin->owned_by=$Member->member->id;
                    $Pin->save();
                }
                
                
            }
        }
       
        $response = array('status' => true,'message'=>'Pins transferred successfully.');  
        return response()->json($response, 200);
    }    

    public function getPinTransferLog(Request $request)
    {
        $user=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $transfer_direction=$request->transfer_direction;

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

        if(!$search &&  !$transfer_direction){           
            $PinTransferLogs=PinTransferLog::select();
            $PinTransferLogs=$PinTransferLogs->where(function ($query)use($user) {
                $query=$query->orWhere('transfered_from',$user->id);
                $query=$query->orWhere('transfered_to',$user->id);
            });

          
            $PinTransferLogs=$PinTransferLogs->with('transfered_from:id,username','transfered_to:id,username','transfered_by:id,username','pin:id,pin_number');
            $PinTransferLogs=$PinTransferLogs->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinTransferLogs=PinTransferLog::select();
            
            $PinTransferLogs=$PinTransferLogs->where(function ($query)use($search) {               
                $query=$query->orWhereHas('pin',function($q)use($search){
                    $q->where('pin_number','like','%'.$search.'%');
                });
            });
            
            if($transfer_direction){
                if($transfer_direction=='from_me'){
                    $PinTransferLogs=$PinTransferLogs->where('transfered_from',$user->id);    
                }

                if($transfer_direction=='to_me'){
                    $PinTransferLogs=$PinTransferLogs->where('transfered_to',$user->id);    
                }
                
            }

            $PinTransferLogs=$PinTransferLogs->with('transfered_from:id,username','transfered_to:id,username','transfered_by:id,username','pin:id,pin_number');
            $PinTransferLogs=$PinTransferLogs->orderBy('id',$sort)->paginate($limit);
        }
       $response = array('status' => true,'message'=>"Pin Transfer Logs retrieved.",'data'=>$PinTransferLogs);
            return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user=JWTAuth::user();
        
        $validate = Validator::make($request->all(), [
            'package_id' => 'required|integer',
            'quantity' => 'required|integer',
            'amount' => "required|regex:/^\d+(\.\d{1,2})?$/",         
            'payment_mode' => 'required|integer',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $PinRequest=new PinRequest;
        $PinRequest->package_id=$request->package_id;
        $PinRequest->quantity=$request->quantity;
        $PinRequest->amount=$request->amount;
        $PinRequest->tax_percentage=$request->tax_percentage;
        $PinRequest->tax_amount=$request->tax_amount;
        $PinRequest->total_amount=$request->total_amount;
        $PinRequest->requested_by=$user->member->id;
        $PinRequest->payment_mode=$request->payment_mode;
        $PinRequest->reference=$request->reference;
        $PinRequest->bank_id=$request->bank_id;  
        $PinRequest->note=$request->note;
        $PinRequest->status='Pending';
        $PinRequest->save();

        $PinRequest=PinRequest::with('package','payment_mode','bank','member.user:username','approver')->find($PinRequest->id);
        $response = array('status' => true,'message'=>'Pin requests created successfully.','data'=>$PinRequest);  
        return response()->json($response, 200);
    }

   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user=JWTAuth::user();
        $PinRequest= PinRequest::where('requested_by',$user->member->id)->find($id);         
        
         if($PinRequest){
            $PinRequest->delete(); 
            $response = array('status' => true,'message'=>'Pin request successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Pin request not found','data' => array());
            return response()->json($response, 404);
        }

    }
}
