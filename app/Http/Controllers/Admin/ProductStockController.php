<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

use App\Models\Admin\StockLogsFiles;
use App\Models\Admin\StockLogs;
use App\Models\Admin\ProductVariant;
use App\Models\Admin\CompanySetting;
use App\Classes\FileUploadHandler;
use JWTAuth;

class ProductStockController extends Controller
{
    
    
    public function getStockLogs(Request $request)
    {

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $inwardoutwordflag=$request->inwardOutwordFlag;

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
        $stockLogs=StockLogs::with('files','product','variant.size','variant.color','order.user')->select();
        
        if($search){
            $stockLogs=$stockLogs->where(function ($query)use($search) {
                $query->orWhere('sku','like','%'.$search.'%');
                $query->orWhereHas('order', function($q)use($search){
                     $q->where('order_no','like','%'.$search.'%');
                });
            });
        }

        if($inwardoutwordflag){
            if($inwardoutwordflag == "Inward"){
                $stockLogs=$stockLogs->orWhere('units_inward','!=',0);
            }else{
                $stockLogs=$stockLogs->orWhere('units_outward','!=',0);
            }    
        }

        $stockLogs=$stockLogs->orderBy('id',$sort)->paginate($limit);

        $response = array('status' => true,'message'=>"Stock Log retrieved.",'data'=>$stockLogs);
        return response()->json($response, 200);
    }

    public function addStock(Request $request){
        $validate = Validator::make($request->all(), [
            'inwardoutward' => 'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $productVaritant = ProductVariant::where('id',$request->sku)->first();
        if($request->inwardoutward == "Inward"){
            $productVaritant->stock = $productVaritant->stock+$request->units_inward;
        }else{
            if($productVaritant->stock<$request->units_outward){
                $response = array('status' => false,'message'=>'Stock is not sufficient for outward','data'=>$validate->messages());
                return response()->json($response, 400);
            }
            $productVaritant->stock = $productVaritant->stock-$request->units_outward;
        }
        $productVaritant->save();

        $stocklogs=new StockLogs;
        $stocklogs->sku = $productVaritant->sku_code;
        $stocklogs->product_id = $productVaritant->product_id;
        $stocklogs->variant_id = $productVaritant->id;

        if($request->inwardoutward == "Inward"){
            $stocklogs->units_inward            = $request->units_inward;
            $stocklogs->inward_challan_number   = $request->inward_challan_number;
        }else{
            $stocklogs->units_outward           = $request->units_outward;
            $stocklogs->outward_challan_number  = $request->outward_challan_number;
        }

        $stocklogs->created_by              = $user=JWTAuth::user()->id;
        $stocklogs->note                    = $request->note;
        $stocklogs->save();

        for($i=0;$i<10;$i++){
            if($request->hasFile('image_'.$i)){
                $fileUploadHandler=new FileUploadHandler;
                $cdn_url=$fileUploadHandler->uploadFile($request,'image_'.$i,'logsfile');
                $stocklogsfiles = new StockLogsFiles;
                $stocklogsfiles->stock_log_id   = $stocklogs->id;
                $stocklogsfiles->url            = $cdn_url;
                $stocklogsfiles->type           = $request->file('image_'.$i)->getClientMimeType();
                $stocklogsfiles->save(); 
            }
        }

        $response = array('status' => true,'message'=>'Stock Log created successfully.','data'=>$productVaritant);  
        return response()->json($response, 200);
    }

    public function getProductStocks(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $product_id=$request->product_id;
        $sku_code=$request->sku_code;
        $low_stock=($request->low_stock=='true')?1:0;
        $low_stock_count=CompanySetting::getValue('low_stock_count');

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

        $ProductVariants=ProductVariant::select();
        
        if($search){
            $ProductVariants=$ProductVariants->where(function ($query)use($search) {
                
                $query->orWhere('sku_code','like','%'.$search.'%');             
                $query->orwhereHas('product',function ($q)use($search) {
                    $q->where('name','like','%'.$search.'%');
                });
                $query->whereHas('product',function ($q) {
                    $q->where('is_active',1);             
                });             
            });        
        }   

        $ProductVariants=$ProductVariants->whereHas('product',function ($q) {
            $q->where('is_active',1);             
        });  

        if($product_id){
            $ProductVariants=$ProductVariants->where('product_id',$product_id);    
        }
        
        if($low_stock){
            $ProductVariants=$ProductVariants->where('stock','<=',$low_stock_count);    
        }

        $ProductVariants=$ProductVariants->with('product','color','size')->orderBy('stock',$sort)->paginate($limit);
        
        $response = array('status' => true,'message'=>"Product stock count retrieved.",'data'=>$ProductVariants);
        return response()->json($response, 200);
    }


}
