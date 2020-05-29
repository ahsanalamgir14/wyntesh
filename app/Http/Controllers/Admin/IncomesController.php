<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Income;
use App\Models\Admin\IncomeParameter;

class IncomesController extends Controller
{    

    //  get Incomes
    public function getIncomes(Request $request)
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
            $Incomes=Income::select()->with('income_parameters');
            $Incomes = $Incomes->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Incomes=Income::select()->with('income_parameters');

            $Incomes=$Incomes->orWhere('name','like','%'.$search.'%');
            $Incomes=$Incomes->orWhere('code','like','%'.$search.'%');
            $Incomes=$Incomes->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Transaction Types retrieved.",'data'=>$Incomes);
            return response()->json($response, 200);
    }
  

    public function createIncome(Request $request){
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'code' => 'required|max:10',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $Income=new Income;
        $Income->name=$request->name;
        $Income->code=$request->code;
        $Income->description=$request->description;
        $Income->capping=$request->capping;
        $Income->is_active=$request->is_active;
        $Income->save();

        $response = array('status' => true,'message'=>'Income created successfully.','data'=>$Income);
        return response()->json($response, 200);
    }

    public function updateIncome(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|max:64',
            'code' => 'required|max:10',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $Income=Income::find($request->id);
        $Income->name=$request->name;
        $Income->code=$request->code;
        $Income->description=$request->description;
        $Income->capping=$request->capping;
        $Income->is_active=$request->is_active;
        $Income->save();


        $response = array('status' => true,'message'=>'Income updated successfully.','data'=>$Income);
        return response()->json($response, 200);
    }

    public function deleteIncome($id){
        $Income= Income::find($id);                 
         if($Income){
            $Income->delete(); 
            $response = array('status' => true,'message'=>'Income successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Income not found','data' => array());
            return response()->json($response, 404);
        }
    }

    public function createIncomeParameter(Request $request){
        
        $validate = Validator::make($request->all(), [
            'income_id' => 'required|integer',
            'name' => 'required|max:64'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $IncomeParameter=new IncomeParameter;
        $IncomeParameter->income_id=$request->income_id;
        $IncomeParameter->name=$request->name;
        $IncomeParameter->value_1=$request->value_1;
        $IncomeParameter->value_2=$request->value_2;
        $IncomeParameter->value_3=$request->value_3;
        $IncomeParameter->value_4=$request->value_4;
        $IncomeParameter->value_5=$request->value_5;
        $IncomeParameter->save();

        $response = array('status' => true,'message'=>'Income Parameter created successfully.','data'=>$IncomeParameter);
        return response()->json($response, 200);
    }

    //  get Incomes
    public function getIncomeParameters(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $income_id=$request->income_id;

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
            $IncomeParameters = IncomeParameter::orderBy('id',$sort)->paginate($limit);    
        }else{
            $IncomeParameters=IncomeParameter::select();
            $IncomeParameters=$IncomeParameters->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');            

            });

            $IncomeParameters=$IncomeParameters->where('income_id',$income_id);
            $IncomeParameters=$IncomeParameters->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Income Parameters retrieved.",'data'=>$IncomeParameters);
            return response()->json($response, 200);
    }


    public function updateIncomeParameter(Request $request){
        
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'income_id' => 'required|integer',
            'name' => 'required|max:64'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $IncomeParameter=IncomeParameter::find($request->id);
        $IncomeParameter->income_id=$request->income_id;
        $IncomeParameter->name=$request->name;
        $IncomeParameter->value_1=$request->value_1;
        $IncomeParameter->value_2=$request->value_2;
        $IncomeParameter->value_3=$request->value_3;
        $IncomeParameter->value_4=$request->value_4;
        $IncomeParameter->value_5=$request->value_5;
        $IncomeParameter->save();

        $response = array('status' => true,'message'=>'Income Parameter updated successfully.','data'=>$IncomeParameter);
        return response()->json($response, 200);
    }

    public function deleteIncomeParameter($id){
        $IncomeParameter= IncomeParameter::find($id);                 
         if($IncomeParameter){
            $IncomeParameter->delete(); 
            $response = array('status' => true,'message'=>'Income Parameter successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Income Parameter not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
