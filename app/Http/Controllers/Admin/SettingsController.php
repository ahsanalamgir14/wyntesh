<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Setting;
use App\Models\Admin\Commission;
use App\Models\Admin\Ledger;
use App\Models\User\User;
use Validator;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
class SettingsController extends Controller
{
    public function downloadFile(Request $request){
        $filename = basename($request->file);
        $tempImage = tempnam(sys_get_temp_dir(), $filename);
        copy($request->file, $tempImage);
        return response()->download($tempImage, $filename);
    }   

    public function get()
    {
        $settings= Setting::get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }

    public function getCopanyDetailsSettings()
    {
        $settings= Setting::orWhere('is_public',1)
        ->get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }


    public function update(Request $request)
    {
        
        $validate = Validator::make($request->all(), [            
            
            'company_name' => "required",                        
            'company_about' => "required",
            'tag_line' => "required|max:255",
            'website' => "required|max:255",
            'contact_email' => "required|max:255|email",
            'contact_phone' => "required",
            'support_email' => "required|max:255|email",
            'support_phone' => "required",
            'address' => "required|max:255",
            'city' => "required|max:255",
            'state' => "required|max:255",
            'country' => "required|max:255",
            'pincode' => "required|integer",
            'facebook_link' => "required",
            'youtube_link' => "required",
            'twitter_link' => "required",
            'instagram_link' => "required",    
            
        ]);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        foreach ($request->all() as $key => $value) {           
                Setting::where('key',$key)->update(['value'=> $value]);
        }

       
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $filename="logo.".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/settings/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/settings/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $BankPartner->logo=$cdn_url;
            $BankPartner->save();
        }
        
        $settings= Setting::get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Settings updated.','data'=>$settings);             
        return response()->json($response, 200);

    }
}
