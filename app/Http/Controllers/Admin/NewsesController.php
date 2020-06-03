<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\News;
use Storage;

class NewsesController extends Controller
{    

    //  get Newses
    public function getNewses(Request $request)
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
            $Newses = News::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Newses=News::select();

            $Newses=$Newses->orWhere('title','like','%'.$search.'%');
            $Newses=$Newses->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Newses retrieved.",'data'=>$Newses);
            return response()->json($response, 200);
    }
  

    public function createNews(Request $request){
        $validate=News::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible == 'true'){
            $is_visible=1;
        }

        $News=new News;
        $News->title=$request->title;
        $News->subtitle=$request->subtitle;
        $News->description=$request->description;
        $News->date=$request->date;
        $News->is_visible=$is_visible;
        $News->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$News->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/news/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/news/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $News->image=$cdn_url;
            $News->save();
        }
       
        $response = array('status' => true,'message'=>'News created successfully.','data'=>$News);
        return response()->json($response, 200);
    }

    public function updateNews(Request $request){        
        
        $validate=News::updateValidator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $is_visible=0;

        if($request->is_visible=='true'){
            $is_visible=1;
        }

        $News=News::find($request->id);
        $News->title=$request->title;
        $News->subtitle=$request->subtitle;
        $News->description=$request->description;
        $News->date=$request->date;
        $News->is_visible=$is_visible;
        $News->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$News->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/news/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/news/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $News->image=$cdn_url;
            $News->save();
        }

        $response = array('status' => true,'message'=>'News updated successfully.','data'=>$News);
        return response()->json($response, 200);
    }

    public function getNews($id){
        $News= News::find($id);  
        if($News){
            $response = array('status' => true,'message'=>"News retrieved.",'data'=>$News);
            return response()->json($response, 200);
        }else{
            $meta = array('status' => false,'message'=>'News not found.');
            $messages = array('data' => array(),'meta'=>$meta);
            return response()->json($messages, 404);
        }
    }

    public function deleteNews($id){
        $News= News::find($id);         
        
         if($News){
            $News->delete(); 
            $response = array('status' => true,'message'=>'News successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'News not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
