<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Models\Admin\Combo;
use Illuminate\Http\Request;
use App\Classes\FileUploadHandler;
use App\Models\Admin\ComboCategory;
use App\Http\Controllers\Controller;

class CombosController extends Controller
{
    public function getCombos(Request $request)
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

        if(!$search){           
            $Combos=Combo::with('categories','categories.category')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Combos=Combo::select();
            $Combos=$Combos->orWhere('name','like','%'.$search.'%');
            $Combos=$Combos->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Combo retrieved.",'data'=>$Combos);
            return response()->json($response, 200);
    }
    public function getUserCombos(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $category_id=$request->category_id;

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

        $Combos=Combo::select();
        
        if($search){
            $Combos=$Combos->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');             
            });            
        }   

        // if($category_id){
        //     $Combos=$Combos->whereHas('categories', function($q)use($category_id){
        //         $q->where('category.id',$category_id);
        //     });    
        // }
     
        $Combos=$Combos->where('is_active',1)->with('categories','categories.category')->orderBy('id','desc')->paginate($limit);
        
        $response = array('status' => true,'message'=>"Combos retrieved.",'data'=>$Combos);
        return response()->json($response, 200);
    }
    public function createCombo(Request $request)
    {
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'combo_code' => 'required|max:32|unique:combos',
            'is_active'=>'required'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        
        $combo=new Combo;
        $combo->name=$request->name;
        $combo->combo_code=$request->combo_code;
        $combo->description=$request->description;
        $combo->base_amount=$request->base_amount;
        $combo->gst_rate=$request->gst_rate;        
        $combo->gst_amount=$request->gst_amount;
        $combo->cgst_rate=$request->cgst_rate;
        $combo->cgst_amount=$request->cgst_amount;
        $combo->sgst_rate=$request->sgst_rate;
        $combo->sgst_amount=$request->sgst_amount;
        $combo->utgst_rate=$request->utgst_rate;
        $combo->utgst_amount=$request->utgst_amount;
        $combo->mrp=$request->mrp;
        $combo->net_amount=$request->net_amount;
        $combo->pv=$request->pv;  
        $combo->is_active=$request->is_active;  
        $combo->save();

        $categories=json_decode($request->categories);

        foreach ($categories as $category) {
            $ComboCategory=new ComboCategory;
            $ComboCategory->combo_id=$combo->id;
            $ComboCategory->category_id=$category->category_id;
            $ComboCategory->quantity=$category->quantity;
            $ComboCategory->dp_base=$category->dp_base;
            $ComboCategory->dp_gst_rate=$category->dp_gst_rate;
            $ComboCategory->dp_gst_amount=$category->dp_gst_amount;
            $ComboCategory->dp_cgst_rate=$category->dp_cgst_rate;
            $ComboCategory->dp_cgst_amount=$category->dp_cgst_amount;
            $ComboCategory->dp_sgst_rate=$category->dp_sgst_rate;
            $ComboCategory->dp_sgst_amount=$category->dp_sgst_amount;
            $ComboCategory->dp_utgst_rate=$category->dp_utgst_rate;
            $ComboCategory->dp_utgst_amount=$category->dp_utgst_amount;
            $ComboCategory->dp_amount=$category->dp_amount;
            $ComboCategory->save();
        }
        if($request->hasFile('image')){
            $fileUploadHandler=new FileUploadHandler;
            $cdn_url=$fileUploadHandler->uploadFile($request,'image','combo');
            $combo->image=$cdn_url;
            $combo->save();
        }

        $combo=Combo::find($combo->id);
        $response = array('status' => true,'message'=>'Combo created successfully.','data'=>$combo);  
        return response()->json($response, 200);
    }
    public function updateCombo(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'combo_code' => 'required|max:32|unique:combos,combo_code,'.$id.',id',
            'is_active'=>'required'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $combo=Combo::find($id);


        if(!$combo){
            $response = array('status' => false,'message'=>'Combo not found');
            return response()->json($response, 404);
        }

        $combo->name=$request->name;
        $combo->combo_code=$request->combo_code;
        $combo->description=$request->description;
        $combo->base_amount=$request->base_amount;
        $combo->gst_rate=$request->gst_rate;        
        $combo->gst_amount=$request->gst_amount;
        $combo->cgst_rate=$request->cgst_rate;
        $combo->cgst_amount=$request->cgst_amount;
        $combo->sgst_rate=$request->sgst_rate;
        $combo->sgst_amount=$request->sgst_amount;
        $combo->utgst_rate=$request->utgst_rate;
        $combo->utgst_amount=$request->utgst_amount;
        $combo->mrp=$request->mrp;
        $combo->net_amount=$request->net_amount;
        $combo->pv=$request->pv;  
        $combo->is_active=$request->is_active;  
        $combo->save();

        $categories=json_decode($request->categories);

        foreach ($categories as $category) {

            $ComboCategory=ComboCategory::where('combo_id',$combo->id)->where('category_id',$category->category_id)->first();

            if(!$ComboCategory){
                $ComboCategory=new ComboCategory;
            }

            $ComboCategory->combo_id=$combo->id;
            $ComboCategory->category_id=$category->category_id;
            $ComboCategory->quantity=$category->quantity;
            $ComboCategory->dp_base=$category->dp_base;
            $ComboCategory->dp_gst_rate=$category->dp_gst_rate;
            $ComboCategory->dp_gst_amount=$category->dp_gst_amount;
            $ComboCategory->dp_cgst_rate=$category->dp_cgst_rate;
            $ComboCategory->dp_cgst_amount=$category->dp_cgst_amount;
            $ComboCategory->dp_sgst_rate=$category->dp_sgst_rate;
            $ComboCategory->dp_sgst_amount=$category->dp_sgst_amount;
            $ComboCategory->dp_utgst_rate=$category->dp_utgst_rate;
            $ComboCategory->dp_utgst_amount=$category->dp_utgst_amount;
            $ComboCategory->dp_amount=$category->dp_amount;
            $ComboCategory->save();
        }

        if($request->hasFile('image')){
            $fileUploadHandler=new FileUploadHandler;
            $cdn_url=$fileUploadHandler->uploadFile($request,'image','combo');
            $combo->image=$cdn_url;
            $combo->save();
        }

        $combo=Combo::find($combo->id);
            $response = array('status' => true,'message'=>'Combo updated successfully.','data'=>$combo);    
        return response()->json($response, 200);        
    }
    public function deleteCombo($id)
    {
        $Combo= Combo::find($id);         
        
         if($Combo){
            $Combo->delete(); 
            $response = array('status' => true,'message'=>'Combo successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Combo not found','data' => array());
            return response()->json($response, 404);
        }

    }
    public function categoryDelete($id)
    {
        $ComboCategory=ComboCategory::find($id);
    
         if($ComboCategory){
            $ComboCategory->delete(); 
            $response = array('status' => true,'message'=>'Combo Category successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Combo Category not found','data' => array());
            return response()->json($response, 404);
        }

    }
    public function changeComboStatus(Request $request){
        $Combo=Combo::find($request->id);

        if($Combo){
            $Combo->is_active=$request->is_active;
            $Combo->save();
            $response = array('status' => true,'message'=>'Combo status changed successfully');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Combo not found');
            return response()->json($response, 400);
        }
    }
}
