<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Superadmin\PaymentMode;
use Storage;

class PaymentModesController extends Controller
{    

    //  get PaymentModes
    public function getPaymentModes(Request $request)
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
            $PaymentModes = PaymentMode::orderBy('id',$sort)->paginate($limit);    
        }else{
            $PaymentModes=PaymentMode::select();

            $PaymentModes=$PaymentModes->orWhere('name','like','%'.$search.'%');
            $PaymentModes=$PaymentModes->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Transaction Types retrieved.",'data'=>$PaymentModes);
            return response()->json($response, 200);
    }
  

    public function createPaymentMode(Request $request){
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $PaymentMode=new PaymentMode;
        $PaymentMode->name=$request->name;
        $PaymentMode->is_active=$request->is_active;
        $PaymentMode->save();

        $response = array('status' => true,'message'=>'Transaction Type created successfully.','data'=>$PaymentMode);
        return response()->json($response, 200);
    }

    public function updatePaymentMode(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $PaymentMode=PaymentMode::find($request->id);
        $PaymentMode->name=$request->name;
        $PaymentMode->is_active=$request->is_active;
        $PaymentMode->save();


        $response = array('status' => true,'message'=>'Transaction Type updated successfully.','data'=>$PaymentMode);
        return response()->json($response, 200);
    }

    public function deletePaymentMode($id){
        $PaymentMode= PaymentMode::find($id);         
        
         if($PaymentMode){
            $PaymentMode->delete(); 
            $response = array('status' => true,'message'=>'PaymentMode successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'PaymentMode not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
