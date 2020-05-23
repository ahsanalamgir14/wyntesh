<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Email;
use App\Models\Admin\Setting;
use Validator;
use JWTAuth;

class EmailsController extends Controller
{
   
    public function getEmails(Request $request)
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
            $Emails=Email::select('id','title');
            $Emails=$Emails->orderBy('id',$sort)->paginate($limit);
        }else{
            $Emails=Email::select('id','title');

            $Emails=$Emails->orWhere('title','like','%'.$search.'%');
            $Emails=$Emails->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Emails retrieved.",'data'=>$Emails);
            return response()->json($response, 200);
    }

    public function getAllEmails()
    {    
        $Emails=Email::select('id','title')->get();
        $response = array('status' => true,'message'=>"Emails retrieved.",'data'=>$Emails);
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

        $Email= new Email;
        $Email->title=$request->title;
        $Email->description=$request->description;
        $Email->is_active=$request->is_active;
        $Email->save();   
        
        $response = array('status' => true,'message'=>'New email created successfully','data'=>$Email);    
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

        $Email=  Email::find($request->id);
        if($Email){
            $Email->title=$request->title;
            $Email->description=$request->description;
            $Email->is_active=$request->is_active;
            $Email->save();
            $response = array('status' => true,'message'=>'Email updated successfully');    
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Email not found');    
            return response()->json($response, 404);
        }        
    }

    
    public function get($id)
    {        
        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $Email= Email::find($id);
        $response = array('status' => true,'message'=>"Email retrieved.",'data'=>$Email,'company_details'=>$settings);
        return response()->json($response, 200);
    }

    public function delete($id){
        $Email= Email::find($id);         
        
         if($Email){
            $Email->delete(); 
            $response = array('status' => true,'message'=>'Email successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Email not found');
            return response()->json($response, 404);
        }
    }

}
