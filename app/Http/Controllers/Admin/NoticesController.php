<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Notice;
use Validator;

class NoticesController extends Controller
{
   
   
    public function save(Request $request)
    {
 
        $validate = Validator::make($request->all(), [
            'title'=>'required|max:1024',
            'description'=>'required|max:2048',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Notice=Notice::first();
        if($Notice){
            $Notice->title=$request->title;
            $Notice->description=$request->description;
            $Notice->is_active=$request->is_active;
            $Notice->save();    
        }else{
            $Notice= new Notice;
            $Notice->title=$request->title;
            $Notice->description=$request->description;
            $Notice->is_active=$request->is_active;
            $Notice->save();    
        }
        
        $response = array('status' => true,'message'=>'Notice saved successfully','data'=>$Notice);    
        return response()->json($response, 200);  
    }

    
    public function get()
    {
        $Notice= Notice::first();
        $response = array('status' => true,'message'=>"Notice retrieved.",'data'=>$Notice);
        return response()->json($response, 200);
    }

   
}
