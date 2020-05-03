<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Package;
use App\Models\Admin\Pin;
use App\Models\Admin\PinRequest;
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
           
            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }else{
            $Pins=Pin::select();
            
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id=JWTAuth::user()->id;
        
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
        $PinRequest->requested_by=$user_id;
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

    public function generatePins(Request $request)
    {
        $user_id=JWTAuth::user()->id;
        
        $validate = Validator::make($request->all(), [
            'package_id' => 'required|integer',
            'quantity' => 'required|integer',
            'base_amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'tax_percentage' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'tax_amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'total_amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
            'owned_by' => 'required|integer',
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
            $response = array('status' => false,'message'=>'Pin request not found','data' => array());
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
            $response = array('status' => false,'message'=>'Pin request not found','data' => array());
            return response()->json($response, 404);
        }

    }
}
