<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User\Address;
use JWTAuth;

class AddressesController extends Controller
{    

    //  get Addresss
    public function getAddresses(Request $request)
    {
        $User=JWTAuth::user();
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
            $Addresss = Address::where('user_id',$User->id)->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Addresss=Address::select();

            $Addresss=$Addresss->orWhere('full_name','like','%'.$search.'%');
            $Addresss=$Addresss->where('user_id',$User->id)->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Addresss retrieved.",'data'=>$Addresss);
            return response()->json($response, 200);
    }
    
    public function getAllAddresses(){
        $User=JWTAuth::user();
        $Address= Address::where('user_id',$User->id)->get();  
        $response = array('status' => true,'message'=>"Addresses retrieved.",'data'=>$Address);
        return response()->json($response, 200);
    }

    public function createAddress(Request $request){
        $User=JWTAuth::user();

        $validate = Validator::make($request->all(), [           
            'full_name' => "required",
            'mobile_number' => "required",
            'pincode' => "required",
            'address' => "required",
            'landmark' => "required",
            'city' => "required",
            'state' => "required",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Address=new Address;
        $Address->full_name=$request->full_name;
        $Address->mobile_number=$request->mobile_number;
        $Address->pincode=$request->pincode;
        $Address->address=$request->address;
        $Address->landmark=$request->landmark;
        $Address->city=$request->city;
        $Address->state=$request->state;
        $Address->user_id=$User->id;
        $Address->save();

        $response = array('status' => true,'message'=>'Address created successfully.','data'=>$Address);
        return response()->json($response, 200);
    }

    public function updateAddress(Request $request){        
        $User=JWTAuth::user();

        $validate = Validator::make($request->all(), [           
            'full_name' => "required",
            'mobile_number' => "required",
            'pincode' => "required",
            'address' => "required",
            'landmark' => "required",
            'city' => "required",
            'state' => "required",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Address=Address::find($request->id);
        if($Address){
            $Address->full_name=$request->full_name;
            $Address->mobile_number=$request->mobile_number;
            $Address->pincode=$request->pincode;
            $Address->address=$request->address;
            $Address->landmark=$request->landmark;
            $Address->city=$request->city;
            $Address->state=$request->state;
            $Address->user_id=$User->id;
            $Address->save();
            
            $response = array('status' => true,'message'=>'Address updated successfully.','data'=>$Address);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Address not found');
            return response()->json($response, 404);
        }
        

        
    }

    public function getAddress($id){
        $User=JWTAuth::user();
        $Address= Address::where('user_id',$User->id)->find($id);  
        if($Address){
            $response = array('status' => true,'message'=>"Address retrieved.",'data'=>$Address);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Address not found');
            return response()->json($response, 404);
        }
    }

    public function deleteAddress($id){
        $User=JWTAuth::user();
        $Address= Address::where('user_id',$User->id)->find($id);         
        
         if($Address){
            $Address->delete(); 
            $response = array('status' => true,'message'=>'Address successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Address not found');
            return response()->json($response, 404);
        }
    }

}
