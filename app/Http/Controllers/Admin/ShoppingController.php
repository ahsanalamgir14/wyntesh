<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Product;
use App\Models\User\Cart;
use App\Models\User\Order;
use App\Models\User\OrderProduct;
use App\Models\User\DeliveryLog;
use Validator;
use JWTAuth;

class ShoppingController extends Controller
{
   
    public function getNewOrders(Request $request)
    {
       
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;

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

        if(!$search && !$date_range ){           
            $Orders=Order::select();
            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name');
            $Orders=$Orders->where('delivery_status','Order Created');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }else{
            $Orders=Order::select();
            
            $Orders=$Orders->where(function ($query)use($search) {
                $query->orWhere('order_no','like','%'.$search.'%');               
                $query=$query->orWhereHas('user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });

            if($date_range){
                $Orders=$Orders->whereDate('created_at','>=', $date_range[0]);
                $Orders=$Orders->whereDate('created_at','<=', $date_range[1]);
            }

            $Orders=$Orders->where('delivery_status','Order Created');

            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$Orders);
        return response()->json($response, 200);
    }

    public function getAllOrders(Request $request)
    {
       
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $date_range=$request->date_range;
        $delivery_status=$request->delivery_status;

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

        if(!$search && !$date_range && !$delivery_status){           
            $Orders=Order::select();
            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }else{
            $Orders=Order::select();
            
            $Orders=$Orders->where(function ($query)use($search) {
                $query->orWhere('order_no','like','%'.$search.'%');  
                $query=$query->orWhereHas('user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });            

            });

            if($delivery_status){
                $Orders=$Orders->where('delivery_status',$delivery_status);
            }

            if($date_range){
                $Orders=$Orders->whereDate('created_at','>=', $date_range[0]);
                $Orders=$Orders->whereDate('created_at','<=', $date_range[1]);
            }

            $Orders=$Orders->with('products','shipping_address','logs','user:id,username,name');
            $Orders=$Orders->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Orders retrieved.",'data'=>$Orders);
        return response()->json($response, 200);
    }

    public function updateOrder(Request $request){
        $User=JWTAuth::user();
        $validate = Validator::make($request->all(), [           
            'delivery_status' => "required",
            'id' => "required|integer",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Order=Order::find($request->id);

        if($Order){
            $Order->delivery_status=$request->delivery_status;
            $Order->delivery_by=$request->delivery_by;
            $Order->tracking_no=$request->tracking_no;
            $Order->remarks=$request->remarks;
            $Order->save();

            $DeliveryLog=new DeliveryLog;
            $DeliveryLog->order_id=$request->id;
            $DeliveryLog->delivery_status=$request->delivery_status;
            $DeliveryLog->remarks=$request->remarks;
            $DeliveryLog->save();

            $response = array('status' => true,'message'=>'Order updated successfully.');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Order not found');
            return response()->json($response, 404);
        }
    }

}
