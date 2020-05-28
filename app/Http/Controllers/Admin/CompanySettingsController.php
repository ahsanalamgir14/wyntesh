<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\CompanySetting;
use Validator;
use JWTAuth;
class CompanySettingsController extends Controller
{ 

    public function get()
    {
        $CompanySettings= CompanySetting::get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Company settings retrived.','data'=>$CompanySettings);             
        return response()->json($response, 200);
    }

    public function update(Request $request)
    {
        
        $validate = Validator::make($request->all(), [                        
            'tds_percentage' => "required",
            'minimum_purchase' => "required",            
        ]);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        foreach ($request->all() as $key => $value) {           
                CompanySetting::where('key',$key)->update(['value'=> $value]);
        }

        
        $CompanySettings= CompanySetting::get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Company Settings updated.','data'=>$CompanySettings);             
        return response()->json($response, 200);

    }
}
