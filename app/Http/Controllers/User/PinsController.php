<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Pin;
use App\Models\Admin\PinRequest;
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
        $user_id=JWTAuth::user()->id;
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
            $PinRequests=$PinRequests->where('requested_by',$user_id);
            $PinRequests=$PinRequests->where('status','Pending');

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user_id);
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
        $user_id=JWTAuth::user()->id;
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
            $PinRequests=$PinRequests->where('requested_by',$user_id);
            $PinRequests=$PinRequests->where('status','Approved');

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user_id);
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
        $user_id=JWTAuth::user()->id;
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
            $PinRequests=$PinRequests->where('requested_by',$user_id);
            $PinRequests=$PinRequests->where('status','Rejected');

            $PinRequests=$PinRequests->with('package:id,name','payment_mode','bank','member:id,user_id','approver:id,name');
            $PinRequests=$PinRequests->orderBy('id',$sort)->paginate($limit);
        }else{
            $PinRequests=PinRequest::select();
            $PinRequests=$PinRequests->where('requested_by',$user_id);
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
        $user_id=JWTAuth::user()->id;
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
            $Pins=$Pins->where('owned_by',$user_id);

            $Pins=$Pins->with('package','owner:id,user_id','user:id,user_id');
            $Pins=$Pins->orderBy('id',$sort)->paginate($limit);
        }else{
            $Pins=Pin::select();
            $Pins=$Pins->where('owned_by',$user_id);

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

   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user_id=JWTAuth::user()->id;
        $PinRequest= PinRequest::where('requested_by',$user_id)->find($id);         
        
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
