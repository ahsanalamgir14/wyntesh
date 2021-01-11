<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Image;
use Storage;
use App\Models\Admin\Product;
use App\Models\Admin\ProductVariant;
use App\Models\Admin\ProductImage;
use App\Classes\FileUploadHandler;

class ProductsController extends Controller
{
   
    
    
    public function getAllProducts()
    {
        $Products=Product::where('is_active',1)->get();
        $response = array('status' => true,'message'=>'Products retrieved.','data'=>$Products);
        return response()->json($response, 200);
    }

    public function getProducts(Request $request)
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

        $Products=Product::select();
        
        if($search){
            $Products=$Products->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');             
            });            
        }   

        if($category_id){
            $Products=$Products->whereHas('categories', function($q)use($category_id){
                $q->where('categories.id',$category_id);
            });    
        }
     
        $Products=$Products->with('categories')->orderBy('id',$sort)->paginate($limit);
        
        $response = array('status' => true,'message'=>"Products retrieved.",'data'=>$Products);
        return response()->json($response, 200);
    }

    public function getUserProducts(Request $request)
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

        $Products=Product::select();
        
        if($search){
            $Products=$Products->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');             
            });            
        }   

        if($category_id){
            $Products=$Products->whereHas('categories', function($q)use($category_id){
                $q->where('categories.id',$category_id);
            });    
        }
     
        $Products=$Products->where('is_active',1)->with('categories')->orderBy('id',$sort)->paginate($limit);
        
        $response = array('status' => true,'message'=>"Products retrieved.",'data'=>$Products);
        return response()->json($response, 200);
    }

    public function createProduct(Request $request){
        $validate = Validator::make($request->all(), [           
            'product_number' => "required|max:32",
            'name' => "required|max:64",
            'qty' => "required|integer",
            'qty_unit' => "required|max:32",
        ]);

        $checkProductNo = Product::where('product_number',$request->product_number)->exists();
        if($checkProductNo){
            $response = array('status' => false,'message'=>'Product code already exists','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Product=new Product;
        $Product->product_number=$request->product_number;
        $Product->name=$request->name;
        $Product->hsn=$request->hsn;
        $Product->brand_name=$request->brand_name;
        $Product->qty=$request->qty;
        $Product->qty_unit=$request->qty_unit;
        $Product->description=$request->description;
        $Product->benefits=$request->benefits;
        $Product->cost_base=$request->cost_base;
        $Product->cost_gst_rate=$request->cost_gst_rate;
        $Product->cost_gst_amount=$request->cost_gst_amount;
        $Product->cost_cgst_rate=$request->cost_cgst_rate;
        $Product->cost_cgst_amount=$request->cost_cgst_amount;
        $Product->cost_sgst_rate=$request->cost_sgst_rate;
        $Product->cost_sgst_amount=$request->cost_sgst_amount;
        $Product->cost_utgst_rate=$request->cost_utgst_rate;
        $Product->cost_utgst_amount=$request->cost_utgst_amount;
        $Product->cost_amount=$request->cost_amount;
        $Product->dp_base=$request->dp_base;
        $Product->dp_gst_rate=$request->dp_gst_rate;
        $Product->dp_gst_amount=$request->dp_gst_amount;
        $Product->dp_cgst_rate=$request->dp_cgst_rate;
        $Product->dp_cgst_amount=$request->dp_cgst_amount;
        $Product->dp_sgst_rate=$request->dp_sgst_rate;
        $Product->dp_sgst_amount=$request->dp_sgst_amount;
        $Product->dp_utgst_rate=$request->dp_utgst_rate;
        $Product->dp_utgst_amount=$request->dp_utgst_amount;
        $Product->dp_amount=$request->dp_amount;
        $Product->retail_base=$request->retail_base;
        $Product->retail_gst_rate=$request->retail_gst_rate;
        $Product->retail_gst_amount=$request->retail_gst_amount;
        $Product->retail_cgst_rate=$request->retail_cgst_rate;
        $Product->retail_cgst_amount=$request->retail_cgst_amount;
        $Product->retail_sgst_rate=$request->retail_sgst_rate;
        $Product->retail_sgst_amount=$request->retail_sgst_amount;
        $Product->retail_utgst_rate=$request->retail_utgst_rate;
        $Product->retail_utgst_amount=$request->retail_utgst_amount;
        $Product->retail_amount=$request->retail_amount;
        $Product->discount_rate=$request->discount_rate?:0;
        $Product->discount_amount=$request->discount_amount?:0;
        $Product->is_active=0;
        $Product->pv=$request->pv;
        $Product->is_color_variant=$request->is_color_variant=="true"?1:0;
        $Product->is_size_variant=$request->is_size_variant=="true"?1:0;
        $Product->is_shipping_waiver=$request->is_shipping_waiver=="true"?1:0;

        $Product->save();

        $cats=explode(',', $request->categories);
        $Product->categories()->sync($cats);

        if($request->hasFile('file')){
            $fileUploadHandler=new FileUploadHandler;
            $cdn_url=$fileUploadHandler->uploadFile($request,'file','products');
            $thumn_cdn_url=$fileUploadHandler->uploadThumbFile($request,'file','products');
            $Product->cover_image=$cdn_url;
            $Product->cover_image_thumbnail=$thumn_cdn_url;
            $Product->save();
        }

        $Product=Product::with('categories','images.variant')->find($Product->id);
        $response = array('status' => true,'message'=>'Product created successfully.','data'=>$Product);
        return response()->json($response, 200);
    }

    public function updateProduct(Request $request){        
        
        $validate = Validator::make($request->all(), [           
            'id' => "required|integer",
            'product_number' => "required|max:32",
            'name' => "required|max:64",
            'qty' => "required|integer",
            'qty_unit' => "required|max:32",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Product= Product::find($request->id);

        if(!$Product){
            $response = array('status' => false,'message'=>'Product not found');
            return response()->json($response, 404);
        }

        $Product->product_number=$request->product_number;
        $Product->name=$request->name;
        $Product->hsn=$request->hsn;
        $Product->brand_name=$request->brand_name;
        $Product->qty=$request->qty;
        $Product->qty_unit=$request->qty_unit;
        $Product->description=$request->description;
        $Product->benefits=$request->benefits;
        $Product->cost_base=$request->cost_base;
        $Product->cost_gst_rate=$request->cost_gst_rate;
        $Product->cost_gst_amount=$request->cost_gst_amount;
        $Product->cost_cgst_rate=$request->cost_cgst_rate;
        $Product->cost_cgst_amount=$request->cost_cgst_amount;
        $Product->cost_sgst_rate=$request->cost_sgst_rate;
        $Product->cost_sgst_amount=$request->cost_sgst_amount;
        $Product->cost_utgst_rate=$request->cost_utgst_rate;
        $Product->cost_utgst_amount=$request->cost_utgst_amount;
        $Product->cost_amount=$request->cost_amount;
        $Product->dp_base=$request->dp_base;
        $Product->dp_gst_rate=$request->dp_gst_rate;
        $Product->dp_gst_amount=$request->dp_gst_amount;
        $Product->dp_cgst_rate=$request->dp_cgst_rate;
        $Product->dp_cgst_amount=$request->dp_cgst_amount;
        $Product->dp_sgst_rate=$request->dp_sgst_rate;
        $Product->dp_sgst_amount=$request->dp_sgst_amount;
        $Product->dp_utgst_rate=$request->dp_utgst_rate;
        $Product->dp_utgst_amount=$request->dp_utgst_amount;
        $Product->dp_amount=$request->dp_amount;
        $Product->retail_base=$request->retail_base;
        $Product->retail_gst_rate=$request->retail_gst_rate;
        $Product->retail_gst_amount=$request->retail_gst_amount;
        $Product->retail_cgst_rate=$request->retail_cgst_rate;
        $Product->retail_cgst_amount=$request->retail_cgst_amount;
        $Product->retail_sgst_rate=$request->retail_sgst_rate;
        $Product->retail_sgst_amount=$request->retail_sgst_amount;
        $Product->retail_utgst_rate=$request->retail_utgst_rate;
        $Product->retail_utgst_amount=$request->retail_utgst_amount;
        $Product->retail_amount=$request->retail_amount;
        $Product->discount_rate=$request->discount_rate?:0;
        $Product->discount_amount=$request->discount_amount?:0;
        $Product->is_color_variant=$request->is_color_variant=="true"?1:0;
        $Product->is_size_variant=$request->is_size_variant=="true"?1:0;
        $Product->is_shipping_waiver=$request->is_shipping_waiver=="true"?1:0;
        $Product->pv=$request->pv;
        $Product->save();

        $cats=explode(',', $request->categories);
        $Product->categories()->sync($cats);

        if($request->hasFile('file')){
            $fileUploadHandler=new FileUploadHandler;
            $cdn_url=$fileUploadHandler->uploadFile($request,'file','products');
            $thumn_cdn_url=$fileUploadHandler->uploadThumbFile($request,'file','products');
            $Product->cover_image=$cdn_url;
            $Product->cover_image_thumbnail=$thumn_cdn_url;
            $Product->save();
        }
        
        $Product=Product::with('categories','images')->find($Product->id);
        
        $response = array('status' => true,'message'=>'Product updated successfully.','data'=>$Product);
        return response()->json($response, 200);
    }

    public function getProduct($id)
    {
       $Product=Product::with('categories','images.variant.color','images.variant.size','variants.color','variants.size')->find($id);
         if($Product){
            $response = array('status' => true,'message'=>'Product retrieved.','data'=>$Product);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Product not found');
            return response()->json($response, 404);
        }
    }

    public function changeProductStatus($id)
    {   
        $Product= Product::find($id);
        if(!$Product->variants->count()){
            $response = array('status' => false,'message'=>'Add product atleast 1 product variant to change status.');
            return response()->json($response, 400);
        }
        if($Product){
            $Product->is_active=$Product->is_active?0:1; 
            $Product->save();
            $response = array('status' => true,'message'=>'Product status successfully changed.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Product not found');
            return response()->json($response, 404);
        }
    }

    public function uploadProductImage(Request $request){        
        
        $validate = Validator::make($request->all(), [           
            'id' => "required|integer",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Product= Product::find($request->id);
        $ProductImage=new ProductImage;
        $ProductImage->product_id=$Product->id;
        $ProductImage->variant_id=$request->variant_id;
        $ProductImage->order=$request->order;
        $ProductImage->is_fetured=$request->is_fetured;
        $ProductImage->save();

        if($request->hasFile('file')){
            $fileUploadHandler=new FileUploadHandler;
            $cdn_url=$fileUploadHandler->uploadFile($request,'file','products');
            $ProductImage->url=$cdn_url;
            $ProductImage->save();
        }
        
        $ProductImage=ProductImage::with('variant.size','variant.color')->find($ProductImage->id);
        $response = array('status' => true,'message'=>'Product image added successfully.','data'=>$ProductImage);
        return response()->json($response, 200);
    }

    public function deleteProductImage($id)
    {
       $ProductImage= ProductImage::find($id);                 
         if($ProductImage){           
            $fileUploadHandler=new FileUploadHandler;
            $fileUploadHandler->deleteFile($ProductImage->url,'products');            
            $ProductImage->delete(); 
            $response = array('status' => true,'message'=>'Product Image successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Product Image not found');
            return response()->json($response, 404);
        }
    }

    public function getAllProductVariantList($id)
    {           
        $productVariant=ProductVariant::with('color','size')->where('product_id',$id)->get();
        $response = array('status' => true,'message'=>"Product variants retrieved.",'data'=>$productVariant);
        return response()->json($response, 200);
    }
    
    public function getAllProductVariant()
    {           
        $productVariant=ProductVariant::with('color','size')->get();    
        $response = array('status' => true,'message'=>"Product variants retrieved.",'data'=>$productVariant);
        return response()->json($response, 200);
    }    

    public function addProductVariant(Request $request){
        $validate = Validator::make($request->all(), [           
            'product_id' => "required",
            'sku' => "required",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        $checkExits = ProductVariant::where('sku_code',$request->sku)->first();
        
        if($checkExits){
             $response = array('status' => false,'message'=>'SKU already exists.');
            return response()->json($response, 400);
        }

        $productVariant=new ProductVariant;
        $productVariant->product_id=$request->product_id;
        $productVariant->color_id=$request->color_variant;
        $productVariant->size_id=$request->size_variant;
        $productVariant->sku_code=$request->sku;
        $productVariant->save();

        $response = array('status' => true,'message'=>'Product variant created successfully.','data'=>$productVariant);
        return response()->json($response, 200);
    }

    public function changeVariantStatus($id) 
    {
        $productVariant= ProductVariant::find($id);                 
        if($productVariant){
            $productVariant->is_active=$productVariant->is_active?0:1; 
            $productVariant->save();
            $response = array('status' => true,'message'=>'Variant status successfully changed.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Variant not found');
            return response()->json($response, 404);
        }
    }    

}
