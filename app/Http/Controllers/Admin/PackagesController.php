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
        $requestData = $request->only('name', 'description','default_period','price','gst','final_price','gst_amount');
        $validate = Validator::make($requestData, [
            'name' => 'required|max:255',
            'default_period' => 'required|integer'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }
        
        
        $package =  Package::create([
            'name' => $requestData['name'],
            'description' => $requestData['description'],
            'price' => $requestData['price'],
            'gst' => $requestData['gst'],
            'gst_amount' => $requestData['gst_amount'],
            'final_price' => $requestData['final_price'],
            'default_period' => $requestData['default_period'],
        ]);

        $courses = $request->get('courses');
        $package->courses()->sync($courses);

         $Package=Package::find($package->id);
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

   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $package = Package::find($id);

        if(empty($package)){
           $response = array('status' => false,'message'=>'Package not found');
            return response()->json($response, 404);
        }

        $requestData = $request->only('name', 'description','default_period','price','gst','final_price','gst_amount');
        
        $validate = Validator::make($requestData, [
            'name' => 'required|max:255',
            'default_period' => 'required|integer'
        ]);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $package->name = $requestData['name'];
        $package->description = $requestData['description'];
        $package->price = $requestData['price'];
        $package->gst = $requestData['gst'];
        $package->gst_amount = $requestData['gst_amount'];
        $package->final_price = $requestData['final_price'];
        $package->default_period = $requestData['default_period'];

        $package->save();

        $courses = $request->get('courses');
        $package->courses()->sync($courses);

        $Package=Package::find($package->id);
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
