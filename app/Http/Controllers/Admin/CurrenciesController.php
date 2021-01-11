<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Currency;
use Validator;

class CurrenciesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
            $Currencies=Currency::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Currencies=Currency::select();
            $Currencies=$Currencies->orWhere('name','like','%'.$search.'%');
            $Currencies=$Currencies->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Currency retrieved.",'data'=>$Currencies);
            return response()->json($response, 200);
    }

    public function all(Request $request)
    {        
        $Currencies=Currency::all();        
        $response = array('status' => true,'message'=>"Currency retrieved.",'data'=>$Currencies);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:32',
            'code' => 'required|max:8|unique:currencies',
            'symbol' => 'required|max:8',
            'conversion_rate' => 'required|integer'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $Currency=new Currency;
        $Currency->name=$request->name;
        $Currency->code=$request->code;
        $Currency->symbol=$request->symbol;
        $Currency->conversion_rate=$request->conversion_rate;
        $Currency->save();

        $Currency=Currency::find($Currency->id);
        $response = array('status' => true,'message'=>'Currency created successfully.','data'=>$Currency);  
        return response()->json($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Currency= Currency::find($id);  
        if($Currency){
            $response = array('status' => true,'message'=>"Currency retrieved.",'data'=>$Currency);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Currency not found');
            return response()->json($response, 404);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:32',
            'code' => 'required|max:8|unique:currencies,code,'.$id.',id',
            'symbol' => 'required|max:8',
            'conversion_rate' => 'required|integer'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Currency=Currency::find($id);

        if(!$Currency){
            $response = array('status' => false,'message'=>'Currency not found');
            return response()->json($response, 404);
        }

        $Currency->name=$request->name;
        $Currency->code=$request->code;
        $Currency->symbol=$request->symbol;
        $Currency->conversion_rate=$request->conversion_rate;
        $Currency->save();


        $Currency=Currency::find($Currency->id);
            $response = array('status' => true,'message'=>'Currency updated successfully.','data'=>$Currency);    
        return response()->json($response, 200);        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Currency= Currency::find($id);                 
         if($Currency){
            $Currency->delete(); 
            $response = array('status' => true,'message'=>'Currency successfully deleted.');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Currency not found','data' => array());
            return response()->json($response, 404);
        }

    }
}
