<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Package;
use App\Models\Admin\Pin;
use App\Models\Admin\PinRequest;
use App\Models\Admin\PinTransferLog;
use App\Models\User\User;
use Validator;
use JWTAuth;
use Carbon\Carbon;
class PinsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingPinRequests(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $package_id=$request->package_id;
        $payment_mode=$request->payment_mode;

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

        if(!$search && !$package_id && !$payment_mode){           
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('status','Pending');

            $PinRequests=$PinRequests->with('package','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('status','Pending');

            $PinRequests=$PinRequests->where(function ($query)use($search) {
                $query=$query->orWhereHas('package',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('payment_mode',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($package_id){
                $PinRequests=$PinRequests->where('package_id',$package_id);                
            }

            if($payment_mode){
                $PinRequests=$PinRequests->where('payment_mode',$payment_mode);                
            }

            $PinRequests=$PinRequests->with('package','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pin requests retrieved.",'data'=>$PinRequests);
            return response()->json($response, 200);
    }

    public function approvedPinRequests(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $package_id=$request->package_id;
        $payment_mode=$request->payment_mode;

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

        if(!$search && !$package_id && !$payment_mode){           
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('status','Approved');

            $PinRequests=$PinRequests->with('package','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('status','Approved');

            $PinRequests=$PinRequests->where(function ($query)use($search) {
                $query=$query->orWhereHas('package',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('payment_mode',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($package_id){
                $PinRequests=$PinRequests->where('package_id',$package_id);                
            }

            if($payment_mode){
                $PinRequests=$PinRequests->where('payment_mode',$payment_mode);                
            }

            $PinRequests=$PinRequests->with('package','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Pin requests retrieved.",'data'=>$PinRequests);
            return response()->json($response, 200);
    }

    public function rejectedPinRequests(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $package_id=$request->package_id;
        $payment_mode=$request->payment_mode;

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

        if(!$search && !$package_id && !$payment_mode){           
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('status','Rejected');

            $PinRequests=$PinRequests->with('package','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('status','Rejected');

            $PinRequests=$PinRequests->where(function ($query)use($search) {
                $query=$query->orWhereHas('package',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('payment_mode',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($package_id){
                $PinRequests=$PinRequests->where('package_id',$package_id);                
            }

            if($payment_mode){
                $PinRequests=$PinRequests->where('payment_mode',$payment_mode);                
            }

            $PinRequests=$PinRequests->with('package','payment_mode','bank','member:id,user_id','approver:id,name');
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

    public function getAllPins(Request $request)
    {
       
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $status=$request->status;
        $package_id=$request->package_id;
        $is_owned=$request->is_owned;
        $used_at_date_range=$request->used_at_date_range;
        $allocated_at_date_range=$request->allocated_at_date_range;

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

        if(!$search && !$status && !$package_id && !$is_owned  && !$used_at_date_range && !$allocated_at_date_range){           
            $Pins=Pin::select();
           
            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }else{
            $Pins=Pin::select();
            
            if($search){
                $Pins=$Pins->where(function ($query)use($search) {
                    $query=$query->orWhere('pin_number',$search);

                    $query=$query->orWhereHas('owner.user',function($q)use($search){
                        $q->where('username','like','%'.$search.'%');
                    });
                });    
            }
            
            if($used_at_date_range){
                $Pins=$Pins->whereDate('used_at','>=', $used_at_date_range[0]);
                $Pins=$Pins->whereDate('used_at','<=', $used_at_date_range[1]);
            }

            if($allocated_at_date_range){
                $Pins=$Pins->whereDate('allocated_at','>=', $allocated_at_date_range[0]);
                $Pins=$Pins->whereDate('allocated_at','<=', $allocated_at_date_range[1]);
            }

            if($status){
                $Pins=$Pins->where('status',$status);                
            }

            if($package_id){
                $Pins=$Pins->where('package_id',$package_id);                
            }

            if($is_owned){
                if($is_owned=='Owned'){
                    $Pins=$Pins->whereNotNull('owned_by');
                }else{                    
                    $Pins=$Pins->whereNull('owned_by');
                }                
            }


            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }
       $response = array('status' => true,'message'=>"Pins retrieved.",'data'=>$Pins);
            return response()->json($response, 200);
    }

    public function getNotUsedPins(Request $request)
    {
       
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $package_id=$request->package_id;
        $is_owned=$request->is_owned;
        $used_at_date_range=$request->used_at_date_range;
        $allocated_at_date_range=$request->allocated_at_date_range;

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

        if(!$search &&  !$package_id && !$is_owned && !$used_at_date_range && !$allocated_at_date_range){           
            $Pins=Pin::select();

            $Pins=$Pins->where('status','Not Used');
            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }else{
            $Pins=Pin::select();
            
            if($search){
                $Pins=$Pins->where(function ($query)use($search) {
                    $query=$query->orWhere('pin_number',$search);

                    $query=$query->orWhereHas('owner.user',function($q)use($search){
                        $q->where('username','like','%'.$search.'%');
                    });
                });    
            }

            if($used_at_date_range){
                $Pins=$Pins->whereDate('used_at','>=', $used_at_date_range[0]);
                $Pins=$Pins->whereDate('used_at','<=', $used_at_date_range[1]);
            }

            if($allocated_at_date_range){
                $Pins=$Pins->whereDate('allocated_at','>=', $allocated_at_date_range[0]);
                $Pins=$Pins->whereDate('allocated_at','<=', $allocated_at_date_range[1]);
            }
            
            if($package_id){
                $Pins=$Pins->where('package_id',$package_id);
            }

            if($is_owned){
                if($is_owned=='Owned'){
                    $Pins=$Pins->whereNotNull('owned_by');
                }else{                    
                    $Pins=$Pins->whereNull('owned_by');
                }                
            }

            $Pins=$Pins->where('status','Not Used');
            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }
       $response = array('status' => true,'message'=>"Pins retrieved.",'data'=>$Pins);
            return response()->json($response, 200);
    }

    public function getPinTransferLog(Request $request)
    {
        
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $transfer_from=$request->transfer_from;
        $transfer_to=$request->transfer_to;

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

        if(!$search &&  !$transfer_from && !$transfer_to){           
            $PinTransferLogs=PinTransferLog::select();
            
            $PinTransferLogs=$PinTransferLogs->with('transfered_from:id,username','transfered_to:id,username','transfered_by:id,username','pin:id,pin_number');
            $PinTransferLogs=$PinTransferLogs->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinTransferLogs=PinTransferLog::select();
            
            $PinTransferLogs=$PinTransferLogs->where(function ($query)use($search) {               
                $query=$query->orWhereHas('pin',function($q)use($search){
                    $q->where('pin_number','like','%'.$search.'%');
                });
            });
            
           
            if($transfer_from){
                $User=User::where('username',$transfer_from)->first();
                $PinTransferLogs=$PinTransferLogs->where('transfered_from',$User->id);    
            }

            if($transfer_to){
                $User=User::where('username',$transfer_to)->first();
                $PinTransferLogs=$PinTransferLogs->where('transfered_to',$User->id);    
            }

            $PinTransferLogs=$PinTransferLogs->with('transfered_from:id,username','transfered_to:id,username','transfered_by:id,username','pin:id,pin_number');
            $PinTransferLogs=$PinTransferLogs->orderBy('id',$sort)->paginate($limit);
        }
       $response = array('status' => true,'message'=>"Pin Transfer Logs retrieved.",'data'=>$PinTransferLogs);
            return response()->json($response, 200);
    }

   

    public function generatePins(Request $request)
    {
        $user_id=JWTAuth::user()->id;
        
        $validate = Validator::make($request->all(), [
            'package_id' => 'required|integer',
            'quantity' => 'required|integer',
            'base_amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'tax_percentage' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'tax_amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'total_amount' => "required|regex:/^\d+(\.\d{1,2})?$/"
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        for ($i=0; $i < $request->quantity; $i++) { 
            $Pin=new Pin;
            $couponPrefix='PIN';
            $pin_number = $couponPrefix.substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
            $Pin->pin_number = $pin_number;
            $Pin->package_id = $request->package_id;
            $Pin->base_amount = $request->base_amount;
            $Pin->tax_percentage = $request->tax_percentage;
            $Pin->tax_amount = $request->tax_amount;
            $Pin->total_amount = $request->total_amount;
            $Pin->owned_by = $request->owned_by;
            $Pin->request_id = $request->request_id;
            $Pin->allocated_at = Carbon::now();
            $Pin->status = 'Not Used';
            $Pin->save();
        }
        
        $PinRequest=PinRequest::find($request->request_id);

        if($PinRequest){
            $PinRequest->approved_at=Carbon::now();
            $PinRequest->approved_by=$user_id;  
            $PinRequest->note=$request->note;
            $PinRequest->status='Approved';
            $PinRequest->save();
        }
        
        $response = array('status' => true,'message'=>'Pins generated successfully.');  
        return response()->json($response, 200);
    }

    public function transferPinsToMember(Request $request)
    {
        $user_id=JWTAuth::user()->id;
        
        $validate = Validator::make($request->all(), [
            'member_id' => 'required|integer',
        ]);

        $Member=User::where('username',$request->member_id)->first();

        if(!$Member){
             $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);
        }

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        foreach ($request->pins as $pin_id) {
            $Pin=Pin::find($pin_id);
            if($Pin){
                
                $transfered_from='';
                if($Pin->owned_by){
                    $Member=Member::find($Pin->owned_by);
                    if($Member->id){
                        $transfered_from=$Member->id;
                    }else{
                        $transfered_from=$user_id;
                    }
                }
                else
                {
                    $transfered_from=$user_id;
                }

                $PinTransferLog=new PinTransferLog;
                $PinTransferLog->pin_id=$pin_id;
                $PinTransferLog->transfered_from=$transfered_from;
                $PinTransferLog->transfered_to=$Member->id;
                $PinTransferLog->transfered_by=$user_id;
                $PinTransferLog->note=$request->note;
                $PinTransferLog->save();

                $Pin->owned_by=$Member->member->id;
                $Pin->save();
            }
        }
       
        $response = array('status' => true,'message'=>'Pins transferred successfully.');  
        return response()->json($response, 200);
    }

    public function getMemberUsedPins(Request $request)
    {
        $User=User::where('username',$request->member_id)->first();

        if(!$User)
        {
            $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);
        }

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $package_id=$request->package_id;
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

        if(!$search &&  !$package_id && !$date_range){           
            $Pins=Pin::select();
            $Pins=$Pins->where('used_by',$User->id);
            $Pins=$Pins->with('package');
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

            if($date_range){
                $Pins=$Pins->whereDate('used_at','>=', $date_range[0]);
                $Pins=$Pins->whereDate('used_at','<=', $date_range[1]);
            }

            $Pins=$Pins->where('used_by',$User->id);
            $Pins=$Pins->with('package');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }
       $response = array('status' => true,'message'=>"Pins retrieved.",'data'=>$Pins);
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
        $PinRequest= PinRequest::find($id);         
        
         if($PinRequest){
            $PinRequest->delete(); 
            $response = array('status' => true,'message'=>'Pin request successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Pin request not found');
            return response()->json($response, 404);
        }

    }

    public function reject(Request $request)
    {
       
        $PinRequest= PinRequest::find($request->id);         
        
         if($PinRequest){
            $PinRequest->status='Rejected';
            $PinRequest->note=$request->note;
            $PinRequest->save(); 
            $response = array('status' => true,'message'=>'Pin request rejected.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Pin request not found');
            return response()->json($response, 404);
        }

    }

    public function checkPin($pin_number)
    {
        $Pin= Pin::where('pin_number',$pin_number)->first();
        if($Pin){
            if($Pin->used_by && $Pin->used_at){
                $response = array('status' => false,'message'=>'Pin is already used.');
                return response()->json($response, 400);
            }

            $response = array('status' => true,'message'=>'Pin is available','package'=>$Pin->package->name);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Pin not found');
            return response()->json($response, 404);
        }      
    }
}
