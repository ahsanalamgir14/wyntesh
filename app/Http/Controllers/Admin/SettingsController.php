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

    public function stats(){
        $courses=1;
        $users=User::count();
        $quizzes=1;
        $topics=1;

        $response = array('status' => true,'message'=>'Stats recieved','stats'=>array('courses'=>$courses,'users'=>$users,'quizzes'=>$quizzes,'topics'=>$topics));             
        return response()->json($response, 200);

    }

    public function franchiseStats(){
        $user_id=JWTAuth::user()->id;
        $balance=0;
        $coupons=1;
        $commission=1;
        $franchises=1;
        $balance_latest=Ledger::where('user_id',$user_id)->latest('id')->first();

        if($balance_latest){
            $balance=$balance_latest->balance;
        }

        $response = array('status' => true,'message'=>'Stats recieved','stats'=>array('franchises'=>$franchises,'coupons'=>$coupons,'balance'=>$balance,'commission'=>$commission));             
        return response()->json($response, 200);

    }

    public function get()
    {
        $settings= Setting::get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }

    public function getCompanySettings()
    {
        $settings= Setting::orWhere('is_public',1)
        ->orWhereIn('key',['is_member_pin_transfer_enabled','tds_percentage'])
        ->get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }

    public function getSettings()
    {
        $settings= Setting::where('is_public',0)
                    ->whereIn('key',['is_member_pin_transfer_enabled','tds_percentage'])
                    ->get()
                    ->pluck('value', 'key')
                    ->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }

    public function updateCompanyDetails(Request $request)
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

    public function update(Request $request)
    {
        
        $validate = Validator::make($request->all(), [            
            
            'name' => "required|max:255",
            'display_name' => "required|max:255",
            'alias' => "required|max:255",
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

            'company_name' => "required",                        
            'company_about' => "required",
            'company_site_link' => "required",
            'facebook_link' => "required",
            'youtube_link' => "required",
            'gplus_link' => "required",
            'twitter_link' => "required",
            'instagram_link' => "required",

            'registration' => "required",
            'coupon' => "required",
            'quiz' => "required",
            'api_login_first' => "required",
            'all_courses_visible' => "required",
            'is_admin_activate_coupon'=>"required",            
            'quiz_questions' => "required",
            'quiz_time' => "required",
            'coupon_prefix' => "required|alpha_num|size:4",
            'coupon_default_view' => "required",
            'mailgun_domain' => "required|max:255",
            'mailgun_secret' => "required|max:255",
            'mail_from' => "required|max:255",
            'mail_from_name' => "required|max:255",            
            
        ]);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        foreach ($request->all() as $key => $value) {           
                Setting::where('key',$key)->update(['value'=> $value]);
        }

       
        if($request->hasFile('watermark_image')){
            $file = $request->file('watermark_image');

            $name="watermark_image.".$file->getClientOriginalExtension();
            $watermark_image='storage/uploads/settings/'.$name;
            
            Setting::where('key','watermark_image')->update(['value'=> $watermark_image]);

            Storage::put(
                'public/uploads/settings/'.$name,
                file_get_contents($request->file('watermark_image')->getRealPath())
            );  
        }
        
        $settings= Setting::get()->pluck('value', 'key')->toArray();
        $response = array('status' => true,'message'=>'Settings updated.','data'=>$settings);             
        return response()->json($response, 200);

    }
}
