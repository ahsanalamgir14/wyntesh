<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Achiever;
use Storage;

class AchieversController extends Controller
{    

    //  get Achievers
    public function getAchievers(Request $request)
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
            $Achievers = Achiever::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Achievers=Achiever::select();

            $Achievers=$Achievers->orWhere('title','like','%'.$search.'%');
            $Achievers=$Achievers->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Achievers retrieved.",'data'=>$Achievers);
            return response()->json($response, 200);
    }
  

    public function createAchiever(Request $request){
        $validate=Achiever::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible == 'true'){
            $is_visible=1;
        }

        $Achiever=new Achiever;
        $Achiever->title=$request->title;
        $Achiever->subtitle=$request->subtitle;
        $Achiever->description=$request->description;
        $Achiever->date=$request->date;
        $Achiever->is_visible=$is_visible;
        $Achiever->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Achiever->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/achiever/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/achiever/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Achiever->image=$cdn_url;
            $Achiever->save();
        }
       
        $response = array('status' => true,'message'=>'Achiever created successfully.','data'=>$Achiever);
        return response()->json($response, 200);
    }

    public function updateAchiever(Request $request){        
        
        $validate=Achiever::updateValidator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible=='true'){
            $is_visible=1;
        }

        $Achiever=Achiever::find($request->id);
        $Achiever->title=$request->title;
        $Achiever->subtitle=$request->subtitle;
        $Achiever->description=$request->description;
        $Achiever->date=$request->date;
        $Achiever->is_visible=$is_visible;
        $Achiever->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Achiever->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/achiever/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/achiever/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Achiever->image=$cdn_url;
            $Achiever->save();
        }

        $response = array('status' => true,'message'=>'Achiever updated successfully.','data'=>$Achiever);
        return response()->json($response, 200);
    }

    public function getAchiever($id){
        $Achiever= Achiever::find($id);  
        if($Achiever){
            $response = array('status' => true,'message'=>"Achiever retrieved.",'data'=>$Achiever);
            return response()->json($response, 200);
        }else{
            $meta = array('status' => false,'message'=>'Achiever not found.');
            $messages = array('data' => array(),'meta'=>$meta);
            return response()->json($messages, 404);
        }
    }

    public function deleteAchiever($id){
        $Achiever= Achiever::find($id);         
        
         if($Achiever){
            $Achiever->delete(); 
            $response = array('status' => true,'message'=>'Achiever successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Achiever not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
