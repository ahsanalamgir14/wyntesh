<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\PayoutType;

class PayoutTypesController extends Controller
{    

    //  get PayoutType
    public function getPayoutTypes(Request $request)
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
            $PayoutType = PayoutType::orderBy('id',$sort)->paginate($limit);    
        }else{
            $PayoutType=PayoutType::select();

            $PayoutType=$PayoutType->orWhere('name','like','%'.$search.'%');
            $PayoutType=$PayoutType->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Transaction Types retrieved.",'data'=>$PayoutType);
            return response()->json($response, 200);
    }
  

    public function createPayoutType(Request $request){
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'exection_type' => 'required|max:32',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $PayoutType=new PayoutType;
        $PayoutType->name=$request->name;
        $PayoutType->exection_type=$request->exection_type;
        $PayoutType->exection_day=$request->exection_day;
        $PayoutType->exection_time=$request->exection_time;
        $PayoutType->save();

        $response = array('status' => true,'message'=>'Transaction Type created successfully.','data'=>$PayoutType);
        return response()->json($response, 200);
    }

    public function updatePayoutType(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'exection_type' => 'required|max:32',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $PayoutType=PayoutType::find($request->id);
        $PayoutType->name=$request->name;
        $PayoutType->exection_type=$request->exection_type;
        $PayoutType->exection_day=$request->exection_day;
        $PayoutType->exection_time=$request->exection_time;
        $PayoutType->save();


        $response = array('status' => true,'message'=>'Transaction Type updated successfully.','data'=>$PayoutType);
        return response()->json($response, 200);
    }

    public function deletePayoutType($id){
        $PayoutType= PayoutType::find($id);         
        
         if($PayoutType){
            $PayoutType->delete(); 
            $response = array('status' => true,'message'=>'PayoutType successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'PayoutType not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
