<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Models\User\User;
use Mail;
use App\Models\Admin\Sms;
use App\Classes\SmsServiceHandler;
use Hash;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getResetToken(Request $request)
    {
        $this->validate($request, ['username' => 'required']);
        
        if ($request->wantsJson()) {
            $user = User::where('username', $request->input('username'))->first();
            if (!$user) {
                $response = array('status' => false,'message'=>trans('passwords.user'));
                return response()->json($response, 404);
            }

            $token = $this->broker()->createToken($user);
            
            $FRONTEND_URL=env('FRONTEND_URL');

            $password_reset_link=$FRONTEND_URL.'/#/reset-password?token='.$token;

            $html='<html>
                Hi, '.$user->name.'<br><br>

                You have requested to reset password on '.env('APP_NAME').'.

                Here is your Password reset link. Click on below link to reset your password <br><br><a href="'.$password_reset_link.'" target=_blank >'.$password_reset_link.'</a>
            </html>';

            $password=$this->genRandomString();

            $Sms=Sms::where('title','Reset Password')->first();        
            if($Sms){
                $sms_content=$Sms->description;
                $sms_content=str_replace("{username}",$user->username,$sms_content);
                $sms_content=str_replace("{password}",$password,$sms_content);
                $SmsServiceHandler=new SmsServiceHandler;
                $SmsServiceHandler->sendSMS($user->contact,$sms_content,0);
            }

            $user->password=Hash::make($password);
            $user->save();

            Mail::send('emails.general',["html"=>$html] , function($message) use ($request,$user){
                $message->to($user->email, $user->name)
                ->subject(env('APP_NAME').': Password Reset');
            });

            $response = array('status' => true,'message'=>'Password reset link sent on your email.');
            return response()->json($response, 200);
        }
    }

    function genRandomString($length = 10)
    {
        if($length < 1)
            $length = 1;
        return substr(preg_replace("/[^A-Za-z0-9]/", '', base64_encode(openssl_random_pseudo_bytes($length * 2))), 0, $length);
    }
}
