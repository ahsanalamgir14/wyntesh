<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\User\Kyc;
use Illuminate\Support\Facades\Hash;
use Validator;
use Carbon\Carbon;
use JWTAuth;

class MembersController extends Controller
{    


    public function getProfile()
    {   
        $id=JWTAuth::user()->id;

        $Member=User::with('kyc')->find($id);
        $response = array('status' => false,'message'=>'Profile data recieved.','data' => $Member);
        return response()->json($response, 200);
    }  

    public function updateProfile(Request $request)
    {   
        $id=JWTAuth::user()->id;

        $Member=User::with('kyc')->find($id);
        if($Member){
            $Member->name=$request->name;
            $Member->contact=$request->contact;
            $Member->gender=$request->gender;
            $Member->dob=$request->dob;
            $Member->save();

            $Kyc=Kyc::where('user_id',$Member->id)->first();
            $Kyc->address=$request->kyc['address'];
            $Kyc->adhar=$request->kyc['adhar'];
            $Kyc->pincode=$request->kyc['pincode'];
            $Kyc->pan=$request->kyc['pan'];
            $Kyc->city=$request->kyc['city'];
            $Kyc->state=$request->kyc['state'];
            $Kyc->bank_ac_name=$request->kyc['bank_ac_name'];
            $Kyc->bank_name=$request->kyc['bank_name'];
            $Kyc->bank_ac_no=$request->kyc['bank_ac_no'];
            $Kyc->ifsc=$request->kyc['ifsc'];
            $Kyc->save();
            
            $Member=User::with('kyc')->find($id);
            $response = array('status' => false,'message'=>'Profile data recieved.','data' => $Member);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Unauthorised, contact admin.');
            return response()->json($response, 401);
        }                
    }    

}
