<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Sms;
use App\Models\Admin\Setting;
use Validator;
use JWTAuth;

class SmsesController extends Controller
{
   
    public function getSmses(Request $request)
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
            $Smses=Sms::select('id','title','is_active','created_at');
            $Smses=$Smses->orderBy('id',$sort)->paginate($limit);
        }else{
            $Smses=Sms::select('id','title','is_active','created_at');

            $Smses=$Smses->orWhere('title','like','%'.$search.'%');
            $Smses=$Smses->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Smss retrieved.",'data'=>$Smses);
            return response()->json($response, 200);
    }

    public function getAllSmses()
    {    
        $Smses=Sms::select('id','title')->get();
        $response = array('status' => true,'message'=>"Smss retrieved.",'data'=>$Smses);
        return response()->json($response, 200);
    }

    public function create(Request $request)
    {
 
        $validate = Validator::make($request->all(), [
            'title'=>'required|max:1024',
            'description'=>'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Sms= new Sms;
        $Sms->title=$request->title;
        $Sms->description=$request->description;
        $Sms->is_active=$request->is_active;
        $Sms->save();   
        
        $response = array('status' => true,'message'=>'New Sms created successfully','data'=>$Sms);    
        return response()->json($response, 200);  
    }

    public function update(Request $request)
    {
 
        $validate = Validator::make($request->all(), [
            'id'=>'required|integer',
            'title'=>'required|max:1024',
            'description'=>'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Sms=  Sms::find($request->id);
        if($Sms){
            $Sms->title=$request->title;
            $Sms->description=$request->description;
            $Sms->is_active=$request->is_active;
            $Sms->save();
            $response = array('status' => true,'message'=>'Sms updated successfully');    
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Sms not found');    
            return response()->json($response, 404);
        }        
    }

    
    public function get($id)
    {        
        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $Sms= Sms::find($id);
        $response = array('status' => true,'message'=>"Sms retrieved.",'data'=>$Sms,'company_details'=>$settings);
        return response()->json($response, 200);
    }

    public function delete($id){
        $Sms= Sms::find($id);         
        
         if($Sms){
            $Sms->delete(); 
            $response = array('status' => true,'message'=>'Sms successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Sms not found');
            return response()->json($response, 404);
        }
    }

}
