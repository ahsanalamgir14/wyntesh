<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Superadmin\TransactionType;
use Storage;

class TransactionTypesController extends Controller
{    

    //  get TransactionTypes
    public function getTransactionTypes(Request $request)
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
            $TransactionTypes = TransactionType::orderBy('id',$sort)->paginate($limit);    
        }else{
            $TransactionTypes=TransactionType::select();

            $TransactionTypes=$TransactionTypes->orWhere('name','like','%'.$search.'%');
            $TransactionTypes=$TransactionTypes->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Transaction Types retrieved.",'data'=>$TransactionTypes);
            return response()->json($response, 200);
    }
  

    public function createTransactionType(Request $request){
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $TransactionType=new TransactionType;
        $TransactionType->name=$request->name;
        $TransactionType->is_active=$request->is_active;
        $TransactionType->save();

        $response = array('status' => true,'message'=>'Transaction Type created successfully.','data'=>$TransactionType);
        return response()->json($response, 200);
    }

    public function updateTransactionType(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $TransactionType=TransactionType::find($request->id);
        $TransactionType->name=$request->name;
        $TransactionType->is_active=$request->is_active;
        $TransactionType->save();


        $response = array('status' => true,'message'=>'Transaction Type updated successfully.','data'=>$TransactionType);
        return response()->json($response, 200);
    }

    public function deleteTransactionType($id){
        $TransactionType= TransactionType::find($id);         
        
         if($TransactionType){
            $TransactionType->delete(); 
            $response = array('status' => true,'message'=>'TransactionType successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'TransactionType not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
