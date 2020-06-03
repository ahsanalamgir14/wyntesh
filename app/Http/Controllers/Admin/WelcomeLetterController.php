<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\WelcomeLetter;
use App\Models\Admin\Setting;
use Validator;
use JWTAuth;

class WelcomeLetterController extends Controller
{
   
   
    public function save(Request $request)
    {
 
        $validate = Validator::make($request->all(), [
            'description'=>'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $WelcomeLetter=WelcomeLetter::first();
        if($WelcomeLetter){
            $WelcomeLetter->description=$request->description;
            $WelcomeLetter->save();    
        }else{
            $WelcomeLetter= new WelcomeLetter;
            $WelcomeLetter->description=$request->description;
            $WelcomeLetter->save();    
        }
        
        $response = array('status' => true,'message'=>'Welcome Letter saved successfully','data'=>$WelcomeLetter);    
        return response()->json($response, 200);  
    }

    
    public function get()
    {
        $user=JWTAuth::user();
        $user_details=array('name' => $user->name,'username'=>$user->username );
        
        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();

        $WelcomeLetter= WelcomeLetter::first();
        $response = array('status' => true,'message'=>"Welcome Letter retrieved.",'data'=>$WelcomeLetter,'company_details'=>$settings,'user'=>$user_details);
        return response()->json($response, 200);
    }

   
}
