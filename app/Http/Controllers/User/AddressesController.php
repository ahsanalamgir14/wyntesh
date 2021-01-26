<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use App\Models\User\Address;

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
        
        $address=Address::select();

        if($search){
            $address=$address->orWhere('full_name','like','%'.$search.'%');
        }
        
        $address=$address->where('user_id',$User->id)->where('is_active',1)->orderBy('id',$sort)->paginate($limit);
   
        $response = array('status' => true,'message'=>"Addresss retrieved.",'data'=>$address);
        return response()->json($response, 200);
    }
    
    public function getAllAddresses(){
        $User=JWTAuth::user();
        $address= Address::where('user_id',$User->id)->where('is_active',1)->get();  
        $response = array('status' => true,'message'=>"Addresses retrieved.",'data'=>$address);
        return response()->json($response, 200);
    }

    public function createAddress(Request $request){
        $User=JWTAuth::user();

        $validate = Validator::make($request->all(), [           
            'full_name' => "required",
            'mobile_number' => "required",
            'door_no' => "required",
            'address' => "required",
            'city' => "required",
            'country' => "required",
            'country_code' => "required",
            'state' => "required",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $address=new Address;
        $address->full_name=$request->full_name;
        $address->mobile_number=$request->mobile_number;
        $address->door_no=$request->door_no;
        $address->pincode=$request->pincode;
        $address->address=$request->address;
        $address->landmark=$request->landmark;
        $address->city=$request->city;
        $address->state=$request->state;
        $address->country=$request->country;
        $address->country_code=$request->country_code;
        $address->user_id=$User->id;
        $address->is_default=$request->is_default?1:0;
        $address->is_active=1;
        $address->save();

        if($request->is_default){
            Address::where('id','!=',$address->id)->where('user_id',$User->id)->update(array('is_default' => 0));
        }

        $response = array('status' => true,'message'=>'Address created successfully.','data'=>$address);
        return response()->json($response, 200);
    }

    public function updateAddress(Request $request){        
        $User=JWTAuth::user();

        $validate = Validator::make($request->all(), [           
            'full_name' => "required",
            'mobile_number' => "required",
            'door_no' => "required",
            'pincode' => "required",
            'address' => "required",
            'city' => "required",
            'country_code' => "required",
            'state' => "required",
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $address=Address::find($request->id);
        if($address){
            $address->full_name=$request->full_name;
            $address->door_no=$request->door_no;
            $address->mobile_number=$request->mobile_number;
            $address->pincode=$request->pincode;
            $address->address=$request->address;
            $address->landmark=$request->landmark;
            $address->city=$request->city;
            $address->state=$request->state;
            $address->country_code=$request->country_code;
            $address->user_id=$User->id;
            $address->is_default=$request->is_default?1:0;
            $address->save();

            if($request->is_default){
                Address::where('id','!=',$address->id)->where('user_id',$User->id)->update(array('is_default' => 0));
            }
            
            $response = array('status' => true,'message'=>'Address updated successfully.','data'=>$address);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Address not found');
            return response()->json($response, 404);
        }
        

        
    }

    public function getAddress($id){
        $User=JWTAuth::user();
        $address= Address::where('user_id',$User->id)->find($id);  
        if($address){
            $response = array('status' => true,'message'=>"Address retrieved.",'data'=>$address);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Address not found');
            return response()->json($response, 404);
        }
    }

    public function deleteAddress($id){
        $User=JWTAuth::user();
        $address= Address::where('user_id',$User->id)->find($id);         
        
         if($address){
            $address->is_active=0;
            $address->save();
            $response = array('status' => true,'message'=>'Address successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Address not found');
            return response()->json($response, 404);
        }
    }

}
