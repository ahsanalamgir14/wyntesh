<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\NotificationSetting;
use Validator;

class NotificationSettingsController extends Controller
{
   
    public function get()
    {
        $settings= NotificationSetting::all();
        $response = array('status' => true,'message'=>'Notification settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }

    public function create(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'name'=>'required'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $alias = str_replace("-", "_", strtolower($request->name));
        $alias = str_replace(" ", "_", $alias);

        $is_exist=NotificationSetting::where('alias',$alias)->first();
        if($is_exist){
            $response = array('status' => false,'message'=>'Setting already exists');
            return response()->json($response, 400);
        }

        $NotificationSetting=  new NotificationSetting;
        $NotificationSetting->name=$request->name;
        $NotificationSetting->alias=$alias;
        $NotificationSetting->is_email=$request->is_email;
        $NotificationSetting->is_sms=$request->is_sms;
        $NotificationSetting->save();
        
        $settings= NotificationSetting::all();
        $response = array('status' => true,'message'=>'Notification settings addded successfully.','data'=>$settings);             
        return response()->json($response, 200);

    }

    public function save(Request $request)
    {


        foreach ($request->all() as $setting) {  
                NotificationSetting::where('id',$setting['id'])->update(['is_email'=> $setting['is_email'],'is_sms'=> $setting['is_sms']]);
        }

        
       
        $settings= NotificationSetting::all();
        $response = array('status' => true,'message'=>'Notification settings saved.','data'=>$settings);             
        return response()->json($response, 200);

    }
}
