<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\SoftwarePopup;
use Storage;


class SoftwarePopupController extends Controller
{
    public function getSoftwarePopups(Request $request)
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
            $Popups = SoftwarePopup::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Popups=SoftwarePopup::select();

            $Popups=$Popups->orWhere('title','like','%'.$search.'%');
            $Popups=$Popups->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Software Popups retrieved.",'data'=>$Popups);
            return response()->json($response, 200);
    }
    public function createSoftwarePopup(Request $request){
        $validate=SoftwarePopup::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible == 'true'){
            $is_visible=1;
        }

        $Popup=new SoftwarePopup;
        $Popup->title=$request->title;
        $Popup->subtitle=$request->subtitle;
        $Popup->description=$request->description;
        $Popup->from_time=$request->from_time;
        $Popup->to_time=$request->to_time;
        $Popup->is_visible=$is_visible;
        $Popup->cta_text=$request->cta_text;
        $Popup->cta_link=$request->cta_link;
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
       
        $response = array('status' => true,'message'=>'Software Popup created successfully.','data'=>$Popup);
        return response()->json($response, 200);
    }

    public function updateSoftwarePopup(Request $request){        
        
        $validate=SoftwarePopup::updateValidator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible=='true'){
            $is_visible=1;
        }

        $Popup=SoftwarePopup::find($request->id);
        $Popup->title=$request->title;
        $Popup->subtitle=$request->subtitle;
        $Popup->description=$request->description;
        $Popup->from_time=$request->from_time;
        $Popup->to_time=$request->to_time;
        $Popup->is_visible=$is_visible;
        $Popup->cta_text=$request->cta_text;
        $Popup->cta_link=$request->cta_link;
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

        $response = array('status' => true,'message'=>'Software Popup updated successfully.','data'=>$Popup);
        return response()->json($response, 200);
    }

    public function getSoftwarePopup($id){
        $Popup= SoftwarePopup::find($id);  
        if($Popup){
            $response = array('status' => true,'message'=>"Software Popup retrieved.",'data'=>$Popup);
            return response()->json($response, 200);
        }else{
            $meta = array('status' => false,'message'=>'Software Popup not found.');
            $messages = array('data' => array(),'meta'=>$meta);
            return response()->json($messages, 404);
        }
    }

    public function deleteSoftwarePopup($id){
        $Popup= SoftwarePopup::find($id);         
        
         if($Popup){
            $Popup->delete(); 
            $response = array('status' => true,'message'=>'Software Popup successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Software Popup not found','data' => array());
            return response()->json($response, 404);
        }
    }
    public function softwarePopup() {
        $Popup= SoftwarePopup::where('is_visible',1)->first();
        $response = array('status' => true,'message'=>'Software Popup retrived.','data'=>$Popup);             
        return response()->json($response, 200);
    }

}
