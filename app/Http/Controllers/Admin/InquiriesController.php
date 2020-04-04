<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Datatables;
use App\Models\Admin\Inquiry;
use Validator;
use Illuminate\Support\Facades\Storage;

class InquiriesController extends Controller
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
        $is_attended=$request->is_attended;

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
            $Inquiries=Inquiry::select();    
            
            if($is_attended!='all'){
                $Inquiries=$Inquiries->where('is_attended',$is_attended);    
            }

            $Inquiries=$Inquiries->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Inquiries=Inquiry::select();

            if($is_attended!='all'){
                $Inquiries=$Inquiries->where('is_attended',$is_attended);    
            }
            
            $Inquiries=$Inquiries->orWhere('name','like','%'.$search.'%');
            $Inquiries=$Inquiries->orWhere('email','like','%'.$search.'%');
            $Inquiries=$Inquiries->orWhere('contact','like','%'.$search.'%');
            $Inquiries=$Inquiries->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Inquirys retrieved.",'data'=>$Inquiries);
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
            'contact'=>'required|max:13',
            'message'=>'required|max:1024',
            'recaptcha' => 'required|recaptcha'
        ],[
        'recaptcha.recaptcha' => 'Invalid captcha.']);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }



        $Inquiry= new Inquiry;
        $Inquiry->name=$request->name;
        $Inquiry->contact=$request->contact;
        $Inquiry->email=$request->email;
        $Inquiry->subject=$request->subject;
        $Inquiry->message=$request->message;
        $Inquiry->save();


        $response = array('status' => true,'message'=>'Inquiry created successfully.');    
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
        $Inquiry= Inquiry::find($id);  
        if($Inquiry){
            $response = array('status' => true,'message'=>"Inquiry retrieved.",'data'=>$Inquiry);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Inquiry not found');
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
        $Inquiry = Inquiry::find($id);
        
        if(!$Inquiry){
            $response = array('status' => false,'message'=>'Inquiry not found');
            return response()->json($response, 404);
        }

        $Inquiry->name=$request->name;
        $Inquiry->contact=$request->contact;
        $Inquiry->email=$request->email;
        $Inquiry->message=$request->message;
        $Inquiry->subject=$request->subject;
        $Inquiry->save();

        $response = array('status' => true,'message'=>'Inquiry updated successfully.','data'=>$Inquiry);    
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
        $Inquiry= Inquiry::find($id);         
        
         if($Inquiry){
            $Inquiry->delete(); 
            $response = array('status' => true,'message'=>'Inquiry successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Inquiry not found','data' => array());
            return response()->json($response, 404);
        }
    }

    public function changeInquiryStatus(Request $request){
        $Inquiry=Inquiry::find($request->id);

        if($Inquiry){
            $Inquiry->is_attended=$request->is_attended;
            $Inquiry->save();
            $response = array('status' => true,'message'=>'Inquiry status changed successfully');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Inquiry not found');
            return response()->json($response, 400);
        }
    }
}
