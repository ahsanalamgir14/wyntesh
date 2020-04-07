<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Download;
use Storage;

class DownloadsController extends Controller
{    

    //  get Downloads
    public function getDownloads(Request $request)
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
            $Downloads = Download::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Downloads=Download::select();

            $Downloads=$Downloads->orWhere('title','like','%'.$search.'%');
            $Downloads=$Downloads->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Downloads retrieved.",'data'=>$Downloads);
            return response()->json($response, 200);
    }
  

    public function createDownload(Request $request){
        $validate=Download::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        

        $Download=new Download;
        $Download->title=$request->title;
        $Download->is_visible=$request->is_visible;
        $Download->url=$request->url;
        $Download->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Download->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/download/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/download/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Download->url=$cdn_url;
            $Download->save();
        }
       
        $response = array('status' => true,'message'=>'Download created successfully.','data'=>$Download);
        return response()->json($response, 200);
    }

    public function updateDownload(Request $request){        
        
        $validate=Download::updateValidator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $Download=Download::find($request->id);
        $Download->title=$request->title;
        $Download->is_visible=$request->is_visible;
        $Download->url=$request->url;
        $Download->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Download->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/download/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/download/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Download->url=$cdn_url;
            $Download->save();
        }

        $response = array('status' => true,'message'=>'Download updated successfully.','data'=>$Download);
        return response()->json($response, 200);
    }

    public function getDownload($id){
        $Download= Download::find($id);  
        if($Download){
            $response = array('status' => true,'message'=>"Download retrieved.",'data'=>$Download);
            return response()->json($response, 200);
        }else{
            $meta = array('status' => false,'message'=>'Download not found.');
            $messages = array('data' => array(),'meta'=>$meta);
            return response()->json($messages, 404);
        }
    }

    public function deleteDownload($id){
        $Download= Download::find($id);         
        
         if($Download){
            $Download->delete(); 
            $response = array('status' => true,'message'=>'Download successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Download not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
