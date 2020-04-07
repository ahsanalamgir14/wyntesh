<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Popup;
use Storage;

class PopupsController extends Controller
{    

    //  get Popups
    public function getPopups(Request $request)
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
            $Popups = Popup::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Popups=Popup::select();

            $Popups=$Popups->orWhere('title','like','%'.$search.'%');
            $Popups=$Popups->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Popups retrieved.",'data'=>$Popups);
            return response()->json($response, 200);
    }
  

    public function createPopup(Request $request){
        $validate=Popup::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible == 'true'){
            $is_visible=1;
        }

        $Popup=new Popup;
        $Popup->title=$request->title;
        $Popup->subtitle=$request->subtitle;
        $Popup->description=$request->description;
        $Popup->from_time=$request->from_time;
        $Popup->to_time=$request->to_time;
        $Popup->is_visible=$is_visible;
        $Popup->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Popup->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/popup/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/popup/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Popup->image=$cdn_url;
            $Popup->save();
        }
       
        $response = array('status' => true,'message'=>'Popup created successfully.','data'=>$Popup);
        return response()->json($response, 200);
    }

    public function updatePopup(Request $request){        
        
        $validate=Popup::updateValidator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible=='true'){
            $is_visible=1;
        }

        $Popup=Popup::find($request->id);
        $Popup->title=$request->title;
        $Popup->subtitle=$request->subtitle;
        $Popup->description=$request->description;
        $Popup->from_time=$request->from_time;
        $Popup->to_time=$request->to_time;
        $Popup->is_visible=$is_visible;
        $Popup->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Popup->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/popup/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/popup/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Popup->image=$cdn_url;
            $Popup->save();
        }

        $response = array('status' => true,'message'=>'Popup updated successfully.','data'=>$Popup);
        return response()->json($response, 200);
    }

    public function getPopup($id){
        $Popup= Popup::find($id);  
        if($Popup){
            $response = array('status' => true,'message'=>"Popup retrieved.",'data'=>$Popup);
            return response()->json($response, 200);
        }else{
            $meta = array('status' => false,'message'=>'Popup not found.');
            $messages = array('data' => array(),'meta'=>$meta);
            return response()->json($messages, 404);
        }
    }

    public function deletePopup($id){
        $Popup= Popup::find($id);         
        
         if($Popup){
            $Popup->delete(); 
            $response = array('status' => true,'message'=>'Popup successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Popup not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
