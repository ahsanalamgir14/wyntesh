<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\Admin\Member;
use App\Models\Admin\ActivationLog;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Validator;
use Carbon\Carbon;
use DB;
use JWTAuth;
class UserAndRoleController extends Controller
{    

    // Users
    public function getUsers(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $is_active=$request->is_active;
        $kyc_status=$request->kyc_status;
        $is_blocked=$request->is_blocked;
        $rank_id=$request->rank_id;

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

        if(!$search && !$kyc_status && !$is_blocked && !$rank_id){
            $users = User::role('user');
          
            if($is_active!='all'){
                $users=$users->where('is_active',$is_active);    
            }

            $users=$users->with('member','member.kyc','member.rank','member.parent.user','member.sponsor.user','member.rank')->orderBy('id',$sort)->paginate($limit);    
        }else{
            
            $users=User::select();

            $users=$users->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');
                $query->orWhere('contact','like','%'.$search.'%');
                $query->orWhere('email','like','%'.$search.'%');
                $query->orWhere('username','like','%'.$search.'%');                

            });

            if($is_active!='all'){
                $users=$users->where('is_active',$is_active);    
            }

            if($is_blocked=='blocked'){
                $users=$users->where('is_blocked',1);    
            }else{
                $users=$users->where('is_blocked',0);    
            }
            
            if($kyc_status){
                $users=$users->whereHas('kyc',function($q)use($kyc_status){
                    $q->where('verification_status',$kyc_status);
                });    
            }


            if($rank_id){
                $rankUsers=Member::where('rank_id',$rank_id)->get()->pluck('user_id')->toArray();
                $users=$users->whereIn('id',$rankUsers);    
            }
            
            $users =$users->role('user');
            $users=$users->with('member','member.kyc','member.parent.user','member.sponsor.user','member.rank')->orderBy('id',$sort)->paginate($limit);
            
        }

       $response = array('status' => true,'message'=>"users retrieved.",'data'=>$users);
            return response()->json($response, 200);
    }
        

    public function createUser(Request $request){
        $validate=User::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $verified_at=Carbon::now();

        $User= User::create([
            'name' => $request->name,
            'username'=>$request->username,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'contact'=>$request->contact,
            'gender'=>$request->gender,
            'dob'=>$request->dob,
            'is_active'=>1,
            'verified_at'=>$verified_at
        ]);

        $User->assignRole('user');
        
        $User=User::with('member.kyc')->find($User->id);
        $response = array('status' => true,'message'=>'User created successfully.','data'=>$User);
        return response()->json($response, 200);
    }

    public function updateUser(Request $request){        
        
        $exist=User::whereNotIn('id',[$request->id])->where('username',[$request->username])->first();

        if($exist){
            $response = array('status' => false,'message'=>'Username already exists.');
            return response()->json($response, 400);
        }

        $validate=User::updateValidator($request);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User= User::find($request->id);
        $total_personal_pv=$request['member']['total_personal_pv'];
        $rank_id=$request['member']['rank_id'];

        if($User){
            $User->name=$request->name;
            $User->contact=$request->contact;
            $User->gender=$request->gender;
            $User->dob=$request->dob;
            $User->email=$request->email;
            

            if($request->password){
                $User->password=Hash::make($request->password);
            }
            
            if($total_personal_pv){
                $User->member->total_personal_pv=$total_personal_pv;
                $User->member->save();
            }
            
            if($rank_id){
                $User->member->rank_id=$rank_id;
                $User->member->save();
            }

            $User->save();

            $User->assignRole('user');

            $User=User::with('member.kyc')->find($User->id);
            $response = array('status' => true,'message'=>'User updated successfully.','data'=>$User);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'User not found.');
            return response()->json($response, 404);
            }            
    }

    public function getUser($id){
        $User= User::find($id);  
        if($User){
            $response = array('status' => true,'message'=>"user retrieved.",'data'=>$User);
            return response()->json($response, 200);
        }else{
            $meta = array('status' => false,'message'=>'User not found.');
            $messages = array('data' => array(),'meta'=>$meta);
            return response()->json($messages, 404);
        }
    }

    public function deleteUser($id){
        $User= User::find($id);         
        
         if($User){
            $User->delete(); 
            $response = array('status' => true,'message'=>'User successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'User not found','data' => array());
            return response()->json($response, 404);
        }
    }

    public function changeUserStatus(Request $request){
        $User=User::find($request->id);

        if($User){
            $User->is_blocked=$request->is_blocked;
            $User->save();
            if($request->is_blocked){
                $message='User blocked successfully.';
            }else{
                $message='User unblocked successfully.';                
            }
            $response = array('status' => true,'message'=>$message);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'User not found');
            return response()->json($response, 400);
        }
    }

    public function changeUserActivationStatus(Request $request){
        $User=User::find($request->user_id);
        $Admin=JWTAuth::user();
        if($User){
            $User->is_active=$request->is_active;
            $User->save();
            $ActivationLog=new ActivationLog;
            $ActivationLog->user_id=$User->id;
            $ActivationLog->is_active=$User->is_active;
            $ActivationLog->by_user=$Admin->id;
            $ActivationLog->remarks=$request->remarks;
            $ActivationLog->save();

            if($request->is_active){
                $message='User activated successfully.';
            }else{
                $message='User deactivated successfully.';                
            }
            $response = array('status' => true,'message'=>$message);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'User not found');
            return response()->json($response, 400);
        }
    }

}
