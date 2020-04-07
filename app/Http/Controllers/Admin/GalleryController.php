<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Gallery;
use Storage;

class GalleryController extends Controller
{    

    //  get Gallery
    public function getGallery(Request $request)
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
            $Gallery = Gallery::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Gallery=Gallery::select();

            $Gallery=$Gallery->orWhere('title','like','%'.$search.'%');
            $Gallery=$Gallery->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Gallery images retrieved.",'data'=>$Gallery);
            return response()->json($response, 200);
    }
  

    public function createGallery(Request $request){
        $validate=Gallery::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Gallery=new Gallery;
        $Gallery->title=$request->title;
        $Gallery->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Gallery->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/gallery/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/gallery/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Gallery->image=$cdn_url;
            $Gallery->save();
        }
       
        $response = array('status' => true,'message'=>'Gallery image created successfully.','data'=>$Gallery);
        return response()->json($response, 200);
    }

    public function updateGallery(Request $request){        
       
        $Gallery=Gallery::find($request->id);
        
        if(!$Gallery){
             $response = array('status' => false,'message'=>'Gallery image not found');
            return response()->json($response, 404);
        }

        $Gallery->title=$request->title;
        $Gallery->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Gallery->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/gallery/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/gallery/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Gallery->image=$cdn_url;
            $Gallery->save();
        }

        $response = array('status' => true,'message'=>'Gallery image updated successfully.','data'=>$Gallery);
        return response()->json($response, 200);
    }

    public function deleteGallery($id){
        $Gallery= Gallery::find($id);         
        
         if($Gallery){
            $Gallery->delete(); 
            $response = array('status' => true,'message'=>'Gallery successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Gallery not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
