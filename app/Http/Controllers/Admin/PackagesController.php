<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Package;
use Validator;

class PackagesController extends Controller
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
            $packages=Package::orderBy('id',$sort)->paginate($limit);    
        }else{
            $packages=Packages::select();
            $packages=$packages->orWhere('name','like','%'.$search.'%');
            $packages=$packages->orderBy('id',$sort)->paginate($limit);
        }

        
       $response = array('status' => true,'message'=>"Packages retrieved.",'data'=>$packages);
            return response()->json($response, 200);
    }

    public function all(Request $request)
    {        
        $packages=Package::all();        
        $response = array('status' => true,'message'=>"Packages retrieved.",'data'=>$packages);
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
            'name' => 'required|max:255',
            'package_code' => 'required|max:32|unique:packages',
            'validity' => 'required|integer'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        $Package=new Package;
        $Package->name=$request->name;
        $Package->package_code=$request->package_code;
        $Package->description=$request->description;
        $Package->base_amount=$request->base_amount;
        $Package->gst_rate=$request->gst_rate;
        $Package->name=$request->name;
        $Package->gst_amount=$request->gst_amount;
        $Package->net_amount=$request->net_amount;
        $Package->capping_amount=$request->capping_amount;
        $Package->pv=$request->pv;  
        $Package->validity=$request->validity;
        $Package->save();

        $Package=Package::find($Package->id);
        $response = array('status' => true,'message'=>'Package created successfully.','data'=>$Package);  
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
        $Package= Package::find($id);  
        if($Package){
            $response = array('status' => true,'message'=>"Package retrieved.",'data'=>$Package);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Package not found');
            return response()->json($response, 404);
        }

    }

    public function changePackageStatus(Request $request){
        $Package=Package::find($request->id);

        if($Package){
            $Package->is_active=$request->is_active;
            $Package->save();
            $response = array('status' => true,'message'=>'Package status changed successfully');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Package not found');
            return response()->json($response, 400);
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
            'name' => 'required|max:255',
            'package_code' => 'required|max:32|unique:packages,package_code,'.$id.',id',
            'validity' => 'required|integer'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Package=Package::find($id);

        if(!$Package){
            $response = array('status' => false,'message'=>'Package not found');
            return response()->json($response, 404);
        }

        $Package->name=$request->name;
        $Package->package_code=$request->package_code;
        $Package->description=$request->description;
        $Package->base_amount=$request->base_amount;
        $Package->gst_rate=$request->gst_rate;
        $Package->name=$request->name;
        $Package->gst_amount=$request->gst_amount;
        $Package->net_amount=$request->net_amount;
        $Package->capping_amount=$request->capping_amount;
        $Package->pv=$request->pv;  
        $Package->validity=$request->validity;
        $Package->save();


        $Package=Package::find($Package->id);
            $response = array('status' => true,'message'=>'Package updated successfully.','data'=>$Package);    
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
        $Package= Package::find($id);         
        
         if($Package){
            $Package->courses()->detach();
            $Package->delete(); 
            $response = array('status' => true,'message'=>'Package successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Package not found','data' => array());
            return response()->json($response, 404);
        }

    }
}
