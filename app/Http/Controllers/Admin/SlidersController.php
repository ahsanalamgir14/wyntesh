<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Slider;
use Storage;

class SlidersController extends Controller
{    

    //  get Sliders
    public function getSliders(Request $request)
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
            $Sliders = Slider::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Sliders=Slider::select();

            $Sliders=$Sliders->orWhere('title','like','%'.$search.'%');
            $Sliders=$Sliders->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Sliders retrieved.",'data'=>$Sliders);
            return response()->json($response, 200);
    }
  

    public function createSlider(Request $request){
        $validate=Slider::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }


        $Slider=new Slider;
        $Slider->title=$request->title;
        $Slider->subtitle=$request->subtitle;
        $Slider->cta_text=$request->cta_text;
        $Slider->cta_link=$request->cta_link;
        $Slider->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Slider->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/slider/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/slider/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Slider->image=$cdn_url;
            $Slider->save();
        }
       
        $response = array('status' => true,'message'=>'Slider created successfully.','data'=>$Slider);
        return response()->json($response, 200);
    }

    public function updateSlider(Request $request){        
        
        

        $Slider=Slider::find($request->id);
        
        if(!$Slider){
            $response = array('status' => false,'message'=>'Slider not found');
            return response()->json($response, 404);
        }

        $Slider->title=$request->title;
        $Slider->subtitle=$request->subtitle;
        $Slider->cta_text=$request->cta_text;
        $Slider->cta_link=$request->cta_link;
        $Slider->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Slider->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/slider/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/slider/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Slider->image=$cdn_url;
            $Slider->save();
        }

        $response = array('status' => true,'message'=>'Slider updated successfully.','data'=>$Slider);
        return response()->json($response, 200);
    }


    public function deleteSlider($id){
        $Slider= Slider::find($id);         
        
         if($Slider){
            $Slider->delete(); 
            $response = array('status' => true,'message'=>'Slider successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Slider not found');
            return response()->json($response, 404);
        }
    }

}
