<?php

namespace App\Classes;
use Illuminate\Http\Request;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;

class SmsServiceHandler 
{    

	public function __construct()
    {
      
    }

    public function sendSMS($contact_no,$content,$is_promotional=0)
    {
        $driver = env('SMS_DRIVER');
        if($driver=='textlocal'){
            return $this->sendTextLocalSMS($contact_no,$content,$is_promotional);
        }else if($driver=='msg91'){            
            return $this->sendMsg91SMS($contact_no,$content,$is_promotional);
        }

    }

    public function sendMsg91SMS($contact_no,$content,$is_promotional=0)
    {
        if($is_promotional){
            config(['msg91.route' => 1]);
        }else{
            config(['msg91.route' => 4]);
        }

        return LaravelMsg91::message($contact_no,$content);  
    }

    public function sendTextLocalSMS($contact_no,$content,$is_promotional=0)
    {
        $apiKey = urlencode(env('TEXTLOCAL_KEY'));
    
        $numbers = array($contact_no);
        $sender = urlencode(env('TEXTLOCAL_SENDER'));
        $message = rawurlencode($content);        
        $numbers = implode(',', $numbers);
 
        // Prepare data for POST request
        //$data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message, 'test'=>true);
        
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
     
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return true;
    }

    public function sendOTP($contact_no)
    {    
        $otp=rand(1000,9999);
        $message="Here is your OTP from ".env('APP_NAME').". OTP -".$otp;
        LaravelMsg91::sendOtp($contact_no, $otp,$message);

        $response = array('status' => true,'message'=>'OTP Sent');
        return response()->json($response, 200);
    }

    public function verifyOTP($contact_no,$otp)
    {    
        $result=LaravelMsg91::verifyOtp($contact_no, (int)$otp);        
        if($result){
            $response = array('status' => true,'message'=>'OTP Verified');
            return response()->json($response, 200);                  
        }else{
            $response = array('status' => false,'message'=>'Invalid OTP');
            return response()->json($response, 400);  
        }

    }

}
