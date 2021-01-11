<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

use App\Models\Admin\SizeVariant;
use App\Models\Admin\ColorVariant;
use App\Models\Admin\ProductVariant;
use App\Models\Admin\Category;
use App\Classes\FileUploadHandler;

use JWTAuth;

class ProductConfigurationsController extends Controller
{
    
    public function getCategories(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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
        
        $categories=Category::select();

        if($search){                       
            $categories=$categories->orWhere('name','like','%'.$search.'%');
        }
        
        $categories=$categories->with('parent')->orderBy('id',$sort)->paginate($limit);
        
        $response = array('status' => true,'message'=>"Categories retrieved.",'data'=>$categories);
        return response()->json($response, 200);
    }

    public function getAllCategories()
    {           
        $categories=Category::all();                  
        $response = array('status' => true,'message'=>"Categories retrieved.",'data'=>$categories);
        return response()->json($response, 200);
    }

    public function createCategory(Request $request){
        $validate = Validator::make($request->all(), [           
            'name' => "required"
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $category=new Category;
        $category->name=$request->name;
        $category->parent_id=$request->parent_id;
        $category->save();
        
        if($request->hasFile('file')){
            $fileUploadHandler=new FileUploadHandler;
            $cdn_url=$fileUploadHandler->uploadFile($request,'file','categories');
            $category->image=$cdn_url;
            $category->save();
        }

        $response = array('status' => true,'message'=>'Category created successfully.','data'=>$category);
        return response()->json($response, 200);
    }

    public function updateCategory(Request $request){        
        
        $validate = Validator::make($request->all(), [           
            'name' => "required"
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $category=Category::find($request->id);
        $category->name=$request->name;
        $category->parent_id=$request->parent_id;
        $category->save();

        if($request->hasFile('file')){
            $fileUploadHandler=new FileUploadHandler;
            $cdn_url=$fileUploadHandler->uploadFile($request,'file','categories');
            $category->image=$cdn_url;
            $category->save();
        }

        $response = array('status' => true,'message'=>'Category updated successfully.','data'=>$category);
        return response()->json($response, 200);
    }

    public function deleteCategory($id)
    {
        $categories= Category::find($id);                 
        if($categories){
            $categories->delete(); 
            $response = array('status' => true,'message'=>'Category successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Category not found');
            return response()->json($response, 404);
        }
    }
    public function allProductVariants(Request $request)
    {        
        $productVariant=ProductVariant::with('product','color','size')->get();
        $response = array('status' => true,'message'=>"Product Variants retrieved.",'data'=>$productVariant);
        return response()->json($response, 200);
    }

    public function getSizeVariant(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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
        $sizeVariant=SizeVariant::select();
		if($search){           
            $sizeVariant=$sizeVariant->orWhere('name','like','%'.$search.'%');
        }
		$sizeVariant=$sizeVariant->orderBy('id',$sort)->paginate($limit);
        $response = array('status' => true,'message'=>"Size Variant retrieved.",'data'=>$sizeVariant);
        return response()->json($response, 200);
    }

    public function getSizeVariantAll(Request $request)
    {        
        $packages=SizeVariant::all();        
        $response = array('status' => true,'message'=>"Size Variant retrieved.",'data'=>$packages);
        return response()->json($response, 200);
    }
    
    public function addSizeVariant(Request $request)
    {
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'brand_size' => 'required|max:32'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $sizeVariant=new SizeVariant;
        $sizeVariant->name=$request->name;
        $sizeVariant->brand_size=$request->brand_size;
        $sizeVariant->length_mm=$request->length_mm;
        $sizeVariant->width_mm=$request->width_mm;
        $sizeVariant->height_mm=$request->height_mm;        
        $sizeVariant->chest_cm=$request->chest_cm;
        $sizeVariant->bust_cm=$request->bust_cm;
        $sizeVariant->to_fit_bust_cm=$request->to_fit_bust_cm;
        $sizeVariant->front_length_cm=$request->front_length_cm;
        $sizeVariant->waist_cm=$request->waist_cm;
        $sizeVariant->to_fit_waist=$request->to_fit_waist;
        $sizeVariant->across_shoulder_cm=$request->across_shoulder_cm;
        $sizeVariant->hips_cm=$request->hips_cm;
        $sizeVariant->inseam_length_cm=$request->inseam_length_cm;
        $sizeVariant->top_length_cm=$request->top_length_cm;
        $sizeVariant->bottom_length_cm=$request->bottom_length_cm;  
        $sizeVariant->sleeve_length_cm=$request->sleeve_length_cm;
        $sizeVariant->save();

        $sizeVariant=SizeVariant::find($sizeVariant->id);
        $response = array('status' => true,'message'=>'Size Variant created successfully.','data'=>$sizeVariant);  
        return response()->json($response, 200);
    }

    public function changeSizeVariantStatus(Request $request){
        $sizeVariant=SizeVariant::find($request->id);

        if($sizeVariant){
            $sizeVariant->is_active=$request->is_active;
            $sizeVariant->save();
            $response = array('status' => true,'message'=>'Size Variant status changed successfully');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Size Variant not found');
            return response()->json($response, 400);
        }
    }
      
    public function updateSizeVariant(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'brand_size' => 'required|max:32'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $sizeVariant=SizeVariant::find($id);

        if(!$sizeVariant){
            $response = array('status' => false,'message'=>'Size Variant not found');
            return response()->json($response, 404);
        }

        $sizeVariant->name=$request->name;
        $sizeVariant->brand_size=$request->brand_size;
        $sizeVariant->length_mm=$request->length_mm;
        $sizeVariant->width_mm=$request->width_mm;
        $sizeVariant->height_mm=$request->height_mm;        
        $sizeVariant->chest_cm=$request->chest_cm;
        $sizeVariant->bust_cm=$request->bust_cm;
        $sizeVariant->to_fit_bust_cm=$request->to_fit_bust_cm;
        $sizeVariant->front_length_cm=$request->front_length_cm;
        $sizeVariant->waist_cm=$request->waist_cm;
        $sizeVariant->to_fit_waist=$request->to_fit_waist;
        $sizeVariant->across_shoulder_cm=$request->across_shoulder_cm;
        $sizeVariant->hips_cm=$request->hips_cm;
        $sizeVariant->inseam_length_cm=$request->inseam_length_cm;
        $sizeVariant->top_length_cm=$request->top_length_cm;
        $sizeVariant->bottom_length_cm=$request->bottom_length_cm;  
        $sizeVariant->sleeve_length_cm=$request->sleeve_length_cm;
        $sizeVariant->save();

        $sizeVariant=SizeVariant::find($sizeVariant->id);
            $response = array('status' => true,'message'=>'Size Variant updated successfully.','data'=>$sizeVariant);    
        return response()->json($response, 200);        
    }
   
    public function deleteSizeVariant($id)
    {
        $sizeVariant= SizeVariant::find($id);         
        
         if($sizeVariant){
            $sizeVariant->delete(); 
            $response = array('status' => true,'message'=>'Size Variant successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Size Variant not found','data' => array());
            return response()->json($response, 404);
        }

    }
    public function getColorVariants(Request $request)
    {

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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
        $colorvariant=ColorVariant::select();
		
		if($search){           
            $colorvariant=$colorvariant->orWhere('name','like','%'.$search.'%');
        }
		$colorvariant=$colorvariant->orderBy('id',$sort)->paginate($limit);
		$response = array('status' => true,'message'=>"Color variant retrieved.",'data'=>$colorvariant);
        return response()->json($response, 200);
    }

    public function getColorVariantAll(Request $request)
    {        
        $colorvariant=ColorVariant::all();        
        $response = array('status' => true,'message'=>"Color variant retrieved.",'data'=>$colorvariant);
        return response()->json($response, 200);
    }

    public function addColorVariant(Request $request)
    {
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'color_code' => 'required|max:32'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $colorvariant=new ColorVariant;
        $colorvariant->name=$request->name;
        $colorvariant->color_code=$request->color_code;
        $colorvariant->note=$request->note;
        $colorvariant->save();
        $colorvariant=ColorVariant::find($colorvariant->id);
        $response = array('status' => true,'message'=>'Color variant created successfully.','data'=>$colorvariant);  
        return response()->json($response, 200);
    }

    public function changeColorVariantStatus(Request $request){
        $colorvariant=ColorVariant::find($request->id);

        if($sizeVariant){
            $colorvariant->is_active=$request->is_active;
            $colorvariant->save();
            $response = array('status' => true,'message'=>'Color Variant status changed successfully');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Color Variant not found');
            return response()->json($response, 400);
        }
    }
      
    public function updateColorVariant(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'color_code' => 'required|max:32'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $colorvariant=ColorVariant::find($id);

        if(!$colorvariant){
            $response = array('status' => false,'message'=>'Color Variant not found');
            return response()->json($response, 404);
        }

        
        $colorvariant->name=$request->name;
        $colorvariant->color_code=$request->color_code;
        $colorvariant->note=$request->note;
       	$colorvariant->save();

        $colorvariant=ColorVariant::find($colorvariant->id);
            $response = array('status' => true,'message'=>'Color Variant updated successfully.','data'=>$colorvariant);    
        return response()->json($response, 200);        
    }
   
    public function deleteColorVariant($id)
    {
        $colorvariant= ColorVariant::find($id);         
        
         if($colorvariant){
            $colorvariant->delete(); 
            $response = array('status' => true,'message'=>'Color Variant successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Color Variant not found','data' => array());
            return response()->json($response, 404);
        }

    }

}
