<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Testimonial;
use Storage;

class TestimonialsController extends Controller
{    

    //  get Testimonials
    public function getTestimonials(Request $request)
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
            $Testimonials = Testimonial::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Testimonials=Testimonial::select();

            $Testimonials=$Testimonials->orWhere('name','like','%'.$search.'%');
            $Testimonials=$Testimonials->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Testimonials retrieved.",'data'=>$Testimonials);
            return response()->json($response, 200);
    }
  

    public function createTestimonial(Request $request){
        $validate=Testimonial::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }


        $Testimonial=new Testimonial;
        $Testimonial->name=$request->name;
        $Testimonial->description=$request->description;
        $Testimonial->subtitle=$request->subtitle;
        $Testimonial->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Testimonial->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/testimonial/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/testimonial/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Testimonial->image=$cdn_url;
            $Testimonial->save();
        }
       
        $response = array('status' => true,'message'=>'Testimonial created successfully.','data'=>$Testimonial);
        return response()->json($response, 200);
    }

    public function updateTestimonial(Request $request){        
        
        

        $Testimonial=Testimonial::find($request->id);
        
        if(!$Testimonial){
            $response = array('status' => false,'message'=>'Testimonial not found');
            return response()->json($response, 404);
        }

        $Testimonial->name=$request->name;
        $Testimonial->subtitle=$request->subtitle;
        $Testimonial->description=$request->description;
        $Testimonial->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Testimonial->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/testimonial/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/testimonial/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Testimonial->image=$cdn_url;
            $Testimonial->save();
        }

        $response = array('status' => true,'message'=>'Testimonial updated successfully.','data'=>$Testimonial);
        return response()->json($response, 200);
    }


    public function deleteTestimonial($id){
        $Testimonial= Testimonial::find($id);         
        
         if($Testimonial){
            $Testimonial->delete(); 
            $response = array('status' => true,'message'=>'Testimonial successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Testimonial not found');
            return response()->json($response, 404);
        }
    }

}
