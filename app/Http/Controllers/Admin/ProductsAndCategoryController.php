<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Product;
use App\Models\Admin\Category;
use App\Models\Admin\ProductImage;
use Validator;
use Image;
use Storage;

class ProductsAndCategoryController extends Controller
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

        if(!$search){           
            $Categories=Category::with('parent')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Categories=Category::select();
            $Categories=$Categories->orWhere('name','like','%'.$search.'%');
            $Categories=$Categories->with('parent')->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Categories retrieved.",'data'=>$Categories);
        return response()->json($response, 200);
    }

    public function getAllCategories()
    {           
        $Categories=Category::all();                  
        $response = array('status' => true,'message'=>"Categories retrieved.",'data'=>$Categories);
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

        $Category=new Category;
        $Category->name=$request->name;
        $Category->parent_id=$request->parent_id;
        $Category->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Category->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/categories/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/categories/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Category->image=$cdn_url;
            $Category->save();
        }
       
        $response = array('status' => true,'message'=>'Category created successfully.','data'=>$Category);
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

        $Category=Category::find($request->id);
        $Category->name=$request->name;
        $Category->parent_id=$request->parent_id;
        $Category->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Category->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/categories/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/categories/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Category->image=$cdn_url;
            $Category->save();
        }

        $response = array('status' => true,'message'=>'Category updated successfully.','data'=>$Category);
        return response()->json($response, 200);
    }

    public function deleteCategory($id)
    {
       $Categories= Categories::find($id);                 
         if($Categories){
            $Categories->delete(); 
            $response = array('status' => true,'message'=>'Category successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Categories not found');
            return response()->json($response, 404);
        }
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

        if(!$search && !$category_id){           
            $Products=Product::with('categories')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Products=Product::select();
            
            $Products=$Products->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');             
            });

            if($category_id){
                $Products=$Products->whereHas('categories', function($q)use($category_id){
                    $q->where('categories.id',$category_id);
                });    
            }
         
            $Products=$Products->with('categories')->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Products retrieved.",'data'=>$Products);
        return response()->json($response, 200);
    }


    public function createProduct(Request $request){
        $validate = Validator::make($request->all(), [           
            'product_number' => "required|max:32",
            'name' => "required|max:64",
            'qty' => "required|integer",
            'qty_unit' => "required|max:32",
            'stock' => "required|integer",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Product=new Product;
        $Product->product_number=$request->product_number;
        $Product->name=$request->name;
        $Product->brand_name=$request->brand_name;
        $Product->qty=$request->qty;
        $Product->qty_unit=$request->qty_unit;
        $Product->description=$request->description;
        $Product->benefits=$request->benefits;
        $Product->gst_rate=$request->gst_rate;
        $Product->cost_base=$request->cost_base;
        $Product->cost_gst=$request->cost_gst;
        $Product->cost_amount=$request->cost_amount;
        $Product->dp_gst_rate=$request->dp_gst_rate;
        $Product->dp_base=$request->dp_base;
        $Product->dp_gst=$request->dp_gst;
        $Product->dp_amount=$request->dp_amount;
        $Product->retail_gst_rate=$request->retail_gst_rate;
        $Product->retail_base=$request->retail_base;
        $Product->retail_gst=$request->retail_gst;
        $Product->retail_amount=$request->retail_amount;
        $Product->discount_rate=$request->discount_rate?:0;
        $Product->discount_amount=$request->discount_amount?:0;
        $Product->admin_fee=$request->admin_fee;
        $Product->shipping_fee=$request->shipping_fee;
        $Product->pv=$request->pv;
        $Product->stock=$request->stock;
        $Product->save();

        $cats=explode(',', $request->categories);
        $Product->categories()->sync($cats);

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Product->id.".".$file->getClientOriginalExtension();
            $thumb=$randomID.'-'.$Product->id."-thumb.".$file->getClientOriginalExtension();

            $thumb_img = Image::make($file);

            $thumb_img->resize(400, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $thumb_resized = $thumb_img->stream()->detach();

            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/products/'.$filename, file_get_contents($file->getRealPath()), 'public');            
            $url=Storage::disk('spaces')->url($project_directory.'/products/'.$filename);            
            $cover_image=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $store=Storage::disk('spaces')->put($project_directory.'/products/'.$thumb, $thumb_resized, 'public');
            $url=Storage::disk('spaces')->url($project_directory.'/products/'.$thumb);            
            $cover_image_thumbnail=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Product->cover_image=$cover_image;
            $Product->cover_image_thumbnail=$cover_image_thumbnail;
            $Product->save();
        }
        
        $Product=Product::with('categories','images')->find($Product->id);
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
            'stock' => "required|integer",
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
        $Product->brand_name=$request->brand_name;
        $Product->qty=$request->qty;
        $Product->qty_unit=$request->qty_unit;
        $Product->description=$request->description;
        $Product->benefits=$request->benefits;
        $Product->gst_rate=$request->gst_rate;
        $Product->cost_base=$request->cost_base;
        $Product->cost_gst=$request->cost_gst;
        $Product->cost_amount=$request->cost_amount;
        $Product->dp_gst_rate=$request->dp_gst_rate;
        $Product->dp_base=$request->dp_base;
        $Product->dp_gst=$request->dp_gst;
        $Product->dp_amount=$request->dp_amount;
        $Product->retail_gst_rate=$request->retail_gst_rate;
        $Product->retail_base=$request->retail_base;
        $Product->retail_gst=$request->retail_gst;
        $Product->retail_amount=$request->retail_amount;
        $Product->discount_rate=$request->discount_rate?:0;
        $Product->discount_amount=$request->discount_amount?:0;
        $Product->admin_fee=$request->admin_fee;
        $Product->shipping_fee=$request->shipping_fee;
        $Product->pv=$request->pv;
        $Product->stock=$request->stock;
        $Product->save();

        $cats=explode(',', $request->categories);
        $Product->categories()->sync($cats);

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Product->id.".".$file->getClientOriginalExtension();
            $thumb=$randomID.'-'.$Product->id."-thumb.".$file->getClientOriginalExtension();

            $thumb_img = Image::make($file);

            $thumb_img->resize(400, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $thumb_resized = $thumb_img->stream()->detach();

            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/products/'.$filename, file_get_contents($file->getRealPath()), 'public');            
            $url=Storage::disk('spaces')->url($project_directory.'/products/'.$filename);            
            $cover_image=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $store=Storage::disk('spaces')->put($project_directory.'/products/'.$thumb, $thumb_resized, 'public');
            $url=Storage::disk('spaces')->url($project_directory.'/products/'.$thumb);            
            $cover_image_thumbnail=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Product->cover_image=$cover_image;
            $Product->cover_image_thumbnail=$cover_image_thumbnail;
            $Product->save();
        }
        
        $Product=Product::with('categories','images')->find($Product->id);
        
        $response = array('status' => true,'message'=>'Product updated successfully.','data'=>$Product);
        return response()->json($response, 200);
    }

    public function getProduct($id)
    {
       $Product=Product::with('categories','images')->find($id);
         if($Product){
            $response = array('status' => true,'message'=>'Product retrieved.','data'=>$Product);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Product not found');
            return response()->json($response, 404);
        }
    }

    public function deleteProduct($id)
    {
       $Product= Product::find($id);                 
         if($Product){
            $Product->delete(); 
            $response = array('status' => true,'message'=>'Product successfully deleted.');             
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

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Product->id.".".$file->getClientOriginalExtension();

            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/products/'.$filename, file_get_contents($file->getRealPath()), 'public');            
            $url=Storage::disk('spaces')->url($project_directory.'/products/'.$filename);            
            $product_image=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $ProductImage->url=$product_image;
            $ProductImage->save();
        }
        
        $response = array('status' => true,'message'=>'Product image added successfully.','data'=>$ProductImage);
        return response()->json($response, 200);
    }

    public function deleteProductImage($id)
    {
       $ProductImage= ProductImage::find($id);                 
         if($ProductImage){
            $project_directory=env('DO_STORE_PATH');
            $filePath=($project_directory.'/products/'.basename($ProductImage->url));            
            Storage::disk('spaces')->delete($filePath);
            $ProductImage->delete(); 
            $response = array('status' => true,'message'=>'Product Image successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Product Image not found');
            return response()->json($response, 404);
        }
    }
}
