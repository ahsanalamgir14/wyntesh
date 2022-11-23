<?php

namespace App\Http\Controllers\Auth;

//use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Crypt;
use Hash;
use Validator;
use Mail;
use GuzzleHttp;
use Config;

use Carbon\Carbon;
use App\Mail\CustomHtmlMail;
use App\Models\User\User;
use App\Models\Admin\Setting;
use App\Models\Admin\CompanySetting;
use App\Http\Controllers\User\ShoppingController;

use JWTAuth;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login','register','emailVerify','mailcheck','google','edu']]);
    }


    function sendVerificationMail(User $User){
        $verification_code=Crypt::encrypt($User->email);

        $FRONTEND_URL=env('FRONTEND_URL');

        $account_verification_link=$FRONTEND_URL.'/#/email-verification/token='.$verification_code;

        $html='<html>
            Hi, '.$User->name.'<br><br>

            Thank you for registering on '.env('APP_NAME').'.

            Here is your account verification link. Click on below link to verify you account. <br><br><a href="'.$account_verification_link.'" target=_blank >Click here to verify Email</a>
        </html>';

        $mail=Mail::to($User->email)->send(new CustomHtmlMail($html));
    }

    function mailcheck(){
        $User=User::find(1);
        $this->sendVerificationMail($User);
    }

    public function register(Request $request){
        $validate=User::validator($request);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User= User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'contact'=>$request->contact,
            'gender'=>$request->gender,
            'dob'=>$request->dob,
            'is_active'=>1,
            'is_blocked'=>0,
            'verified_at'=>null
        ]);

        $this->sendVerificationMail($User);
        $User->assignRole('user');
        $response = array('status' => true,'message'=>'You are registered successfully, verification email is sent to your email account.');
        return response()->json($response, 200);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $credentials = request(['username','email', 'password']);
        $is_maintenance=CompanySetting::getValue('is_maintenance');

        $username=isset($request->username)?$request->username:'';
        $email=isset($request->email)?$request->email:'';
        $password=$request->password;

        if(!empty($email)  && !empty($password)){
            $credentials=['email' => $credentials['email'], 'password' => $credentials['password']];
        }else if(!empty($username)  && !empty($password)){
            $credentials=['username' => $credentials['username'], 'password' => $credentials['password']];
        }else {
            return response()->json(['status'=>false,'message' => 'Email or username and password required'], 401);            
        }

       
        if ($token = JWTAuth::attempt($credentials))
        {
            $user=JWTAuth::user();
            if(!isset($contact)){
                if($user->verified_at==null){
                    return response()->json(['status'=>false,'message' => 'Email not verfied, verify your email first.'], 401);   
                }
            }

            if($user->is_blocked==1){
                return response()->json(['status'=>false,'message' => 'You are blocked, kindly contact admin.'], 401);   
            }
                
            if($user->roles[0]->name=='user' && $is_maintenance){
                return response()->json(['status'=>false,'message' => 'Website maintenance going on.'], 401);
            }

            return $this->respondWithToken($token);
        }else{
            return response()->json(['status'=>false,'message' => 'invalid_credentials'], 401);            
        }
            
    }

    public function edu(Request $request){

        $credentials = request(['username', 'password']);
        $username=isset($request->username)?$request->username:'';
        $password=$request->password;
      
        if(!empty($username)  && !empty($password)){
            $credentials=['username' => $credentials['username'], 'password' => $credentials['password']];
        }else {
            return response()->json(['status'=>false,'message' => 'Username and password required'], 401);            
        }

        if ($token = JWTAuth::attempt($credentials))
        {
            $user=JWTAuth::user();

            if($user->roles[0]->name != 'user'){
                return response()->json(['status'=>false,'message' => 'Only Member login allowed'], 401);
            }

            $ShoppingController=new ShoppingController;
            $package=$ShoppingController->getUserCourses($user);

            $userinfo['name']           = $user->name;
            $userinfo['joining_date']   = date('d-m-Y h:i:s',strtotime($user->created_at)); ;
            $userinfo['dob']            = date('d-m-Y',strtotime($user->dob));
            $userinfo['contact']        = $user->contact;
            $userinfo['email']          = $user->email;              
            $userinfo['package']        = $package;  
            
            $response = array('status' => true,'message'=>"Education info.",'data'=>$userinfo);
            return response()->json($response, 400);
        }else{
            return response()->json(['status'=>false,'message' => 'invalid_credentials'], 401);            
        }
    }

    public function admin_login(Request $request)
    {   

        $token = $request->token;
        JWTAuth::setToken($token);
        try {
                $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                $response = array('status' => false,'message'=>'Token is Invalid');
                return response()->json($response, 401);
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                $response = array('status' => false,'message'=>'Token is Invalid');
                return response()->json($response, 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                $response = array('status' => false,'message'=>'Token is Expired');
                return response()->json($response, 401);
            }else{
                $response = array('status' => false,'message'=>'Authorization Token not found');
                return response()->json($response, 401);
            }
        }
        if($user->roles[0]->name!='admin'){
            return response()->json(['status'=>false,'message' => 'You do not have enough rights'], 401);
        }

        $username=isset($request->username)?$request->username:'';
        $User=User::where('username',$username)->first();

       
        if ($User)
        {
            $token=JWTAuth::fromUser($User);            
            return $this->respondWithToken($token);
        }else{
            return response()->json(['status'=>false,'message' => 'invalid_credentials'], 401);            
        }
            
    }



    public function emailVerify(Request $request)
    {
       
        $validate = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        
        if ($validate->fails()) {
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $token = $request->token;

        $verification_token = Crypt::decrypt($request->token);
        
        $user = User::where('email',$verification_token)->first();
        $email_verified_at=Carbon::now();

        if($user) {
            $user->verified_at = $email_verified_at;
            $user->save();
            return 'Your email is verfied.';
            $response = array('status' =>true ,'message'=>'Account successfully verified');
            return response()->json($response, 200);

        }else{
            $response = array('status' =>false ,'message'=>'Invalid verification token');
            return response()->json($response, 401);
        }
    }

    public function changePassword(Request $request)
    {   
        $validate = Validator::make($request->all(), [
            'password' => 'required'
        ]);
        
        if ($validate->fails()) {
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User=JWTAuth::user();
        if($User){
             $User->password=Hash::make($request->password);
             $User->save();
             $response = array('status' =>true ,'message'=>'Password changed successfully.');
            return response()->json($response, 200);         
        }else{
            $response = array('status' =>false ,'message'=>'User not found');
            return response()->json($response, 404);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {        
        $user=JWTAuth::user();
        
        $roles = $user->getRoleNames();
        $permissions = $user->getPermissionNames();

        $user_data=array('id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'contact'=>$user->contact,'dob'=>$user->dob,'roles'=>$roles ,'currency'=>$user->currency,'permissions'=>$permissions);
        
        return response()->json($user_data);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        return response()->json(['status'=>true,'message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        JWTAuth::setToken($token);
        $user=JWTAuth::toUser($token);
        
        $roles = $user->getRoleNames();
        $permissions = $user->getPermissionNames();
       
        $user_data=array('id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'contact'=>$user->contact,'dob'=>$user->dob,'roles'=>$roles ,'currency'=>$user->currency,'permissions'=>$permissions);

        return response()->json([            
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user'=>$user_data,
            
        ]);
    }

    public function google(Request $request)
    {
        $client = new GuzzleHttp\Client();
        $params = [
            'code' => $request->input('code'),
            'client_id' => $request->input('clientId'),
            'client_secret' => Setting::where('key','google_client_secret')->first()->value,
            'redirect_uri' => $request->input('redirectUri'),
            'grant_type' => 'authorization_code',
        ];
        // Step 1. Exchange authorization code for access token.
        $accessTokenResponse = $client->request('POST', 'https://accounts.google.com/o/oauth2/token', [
            'form_params' => $params
        ]);
        $accessToken = json_decode($accessTokenResponse->getBody(), true);
        // Step 2. Retrieve profile information about the current user.
        $profileResponse = $client->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo/', [
            'headers' => array('Authorization' => 'Bearer ' . $accessToken['access_token'])
        ]);
        $profile = json_decode($profileResponse->getBody(), true);
        // Step 3a. If user is already signed in then link accounts.
        if ($request->header('Authorization'))
        {
            $user = User::where('google', '=', $profile['sub']);
            if ($user->first())
            {
                return response()->json(['message' => 'There is already a Google account that belongs to you'], 409);
            }
            $token = explode(' ', $request->header('Authorization'))[1];
            $user = User::find($user->id);
            $user->google = $profile['sub'];
            $user->name = $user->name ?: $profile['name'];
            $user->verified_at = date('Y-m-d H:i:s');
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            $token=JWTAuth::fromUser($user);
            return $this->respondWithToken($token);
        }
        // Step 3b. Create a new user account or return an existing one.
        else
        {
            $user = User::where('email', '=', $profile['email'])->first();

            if ($user)
            {
                $user->google = $profile['sub'];
                $user->verified_at = date('Y-m-d H:i:s');
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
                $token=JWTAuth::fromUser($user);
                return $this->respondWithToken($token);
            }
            
            $user = new User;
            $user->google = $profile['sub'];
            $user->name = $profile['name'];
            $user->email = $profile['email'];
            $user->username = $profile['email'];
            $user->verified_at = date('Y-m-d H:i:s');
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            $user->assignRole('user');
            $token=JWTAuth::fromUser($user);
            return $this->respondWithToken($token);
        }
    }


}