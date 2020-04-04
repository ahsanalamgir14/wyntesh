<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Datatables;
use App\Models\Admin\Subscriber;
use Validator;
use Illuminate\Support\Facades\Storage;

class SubscribersController extends Controller
{
   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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
            $Subscribers=Subscriber::select();    
            

            $Subscribers=$Subscribers->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Subscribers=Subscriber::select();

            
            $Subscribers=$Subscribers->orWhere('name','like','%'.$search.'%');
            $Subscribers=$Subscribers->orWhere('email','like','%'.$search.'%');
            $Subscribers=$Subscribers->orWhere('contact','like','%'.$search.'%');
            $Subscribers=$Subscribers->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Subscribers retrieved.",'data'=>$Subscribers);
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

        
        $validate = Validator::make($request->all(), [
            'name'=>'required|max:64',
            'recaptcha' => 'required|recaptcha'
        ],[
        'recaptcha.recaptcha' => 'Invalid captcha.']);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }



        $Subscriber= new Subscriber;
        $Subscriber->name=$request->name;
        $Subscriber->contact=$request->contact;
        $Subscriber->email=$request->email;
        $Subscriber->save();


        $response = array('status' => true,'message'=>'Subscriber created successfully.');    
        return response()->json($response, 200);  
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Subscriber= Subscriber::find($id);  
        if($Subscriber){
            $response = array('status' => true,'message'=>"Subscriber retrieved.",'data'=>$Subscriber);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Subscriber not found');
            return response()->json($response, 404);
        }
    }

   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $Subscriber = Subscriber::find($id);
        
        if(!$Subscriber){
            $response = array('status' => false,'message'=>'Subscriber not found');
            return response()->json($response, 404);
        }

        $Subscriber->name=$request->name;
        $Subscriber->contact=$request->contact;
        $Subscriber->email=$request->email;
        $Subscriber->save();

        $response = array('status' => true,'message'=>'Subscriber updated successfully.','data'=>$Subscriber);    
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
        $Subscriber= Subscriber::find($id);         
        
         if($Subscriber){
            $Subscriber->delete(); 
            $response = array('status' => true,'message'=>'Subscriber successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Subscriber not found','data' => array());
            return response()->json($response, 404);
        }
    }
}
