<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\User\Kyc;
use App\Models\Admin\Member;
use Illuminate\Support\Facades\Hash;
use Validator;
use Carbon\Carbon;
use JWTAuth;
use DB;
use Storage;

class MembersController extends Controller
{    


    public function getProfile()
    {   
        $id=JWTAuth::user()->id;

        $Member=User::with('kyc')->find($id);
        $response = array('status' => true,'message'=>'Profile data recieved.','data' => $Member);
        return response()->json($response, 200);
    } 

    public function checkSponsorCode($code)
    {
        $Member=User::select('id','name')->where('username',$code)->role('user')->with('member:id,user_id')->first();
        if($Member){
            $response = array('status' => true,'message'=>'Sponsor recieved.','data' => $Member);
            return response()->json($response, 200);  
        }else{
            $response = array('status' => false,'message'=>'Sponsor not found','data' => $Member);
            return response()->json($response, 404);  
        }
        
    }

    public function checkMemberCode($code)
    {
        $Member=User::select('id','name')->where('username',$code)->role('user')->with('member:id,user_id')->first();
        if($Member){
            $response = array('status' => true,'message'=>'Member recieved.','data' => $Member);
            return response()->json($response, 200);  
        }else{
            $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);  
        }
        
    }

    public function checkMemberBalance($code){
        $Member=User::select('id','name')->where('username',$code)->role('user')->with('member:id,user_id,wallet_balance')->first();
        if($Member){
            $response = array('status' => true,'message'=>'Member recieved.','data' => $Member);
            return response()->json($response, 200);  
        }else{
            $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);  
        }
    }

    public function adminGeneology(){
        $zero=Member::with('children.children.children')->with('kyc')->with('user')->where('level',0)->first();
        $response = array('status' => false,'message'=>'Geneology recieved.','data' => $zero);
        return response()->json($response, 200);  
    }

    public function adminMemberGeneology($id){
        $User=User::where('username',$id)->first();
        if($User){
            $zero=Member::with('children.children.children')->with('kyc')->with('user')->where('user_id',$User->id)->first();
            $response = array('status' => false,'message'=>'Geneology recieved.','data' => $zero);
            return response()->json($response, 200);  
        }else{
            $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);
        }
        
    }

    public function myGeneology(){
        $id=JWTAuth::user()->id;
        $zero=Member::with('children.children.children')->with('kyc')->with('user')->where('user_id',$id)->first();
        $response = array('status' => false,'message'=>'Geneology recieved.','data' => $zero);
        return response()->json($response, 200);  
    }

    public function myMemberGeneology($id){
        $user_id=JWTAuth::user()->id;
        $my_member_id=JWTAuth::user()->member->id;
        $User=User::where('username',$id)->first();

        if($User){
            $tempMember=Member::where('user_id',$User->id)->first();            
            $pathArray=(explode("/",$tempMember->path));            
            if(in_array($my_member_id,$pathArray)){
                $zero=Member::with('children.children.children')->with('kyc')->with('user')->where('user_id',$User->id)->first();
                $response = array('status' => false,'message'=>'Geneology recieved.','data' => $zero);
                return response()->json($response, 200);      
            }else{
                $response = array('status' => false,'message'=>'Member not in your downline');
                return response()->json($response, 404);    
            }            
        }else{
            $response = array('status' => false,'message'=>'Member not in your downline');
            return response()->json($response, 404);
        }
        
    }

    public function updateProfile(Request $request)
    {   
        $id=JWTAuth::user()->id;

        $User=User::find($id);
        
        if($User){
            $User->name=$request->name;
            $User->contact=$request->contact;
            $User->gender=$request->gender;
            $User->dob=$request->dob;
            $User->save();

            $kyc=json_decode($request->kyc,true);

            $User->kyc->address=$kyc['address'];
            $User->kyc->adhar=$kyc['adhar'];
            $User->kyc->pincode=$kyc['pincode'];
            $User->kyc->pan=$kyc['pan'];
            $User->kyc->city=$kyc['city'];
            $User->kyc->state=$kyc['state'];
            $User->kyc->bank_ac_name=$kyc['bank_ac_name'];
            $User->kyc->bank_name=$kyc['bank_name'];
            $User->kyc->bank_ac_no=$kyc['bank_ac_no'];
            $User->kyc->ifsc=$kyc['ifsc'];
            $User->kyc->verification_status=$kyc['verification_status'];
            $User->kyc->save();

            if($request->hasFile('adhar_image')){
                $file = $request->file('adhar_image');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$User->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $User->kyc->adhar_image=$cdn_url;
                $User->kyc->save();
            }

            if($request->hasFile('pan_image')){
                $file = $request->file('pan_image');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$User->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $User->kyc->pan_image=$cdn_url;
                $User->kyc->save();
            }

            if($request->hasFile('cheque_image')){
                $file = $request->file('cheque_image');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$User->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $User->kyc->cheque_image=$cdn_url;
                $User->kyc->save();
            }

            
            $Member=User::with('kyc')->find($id);
            $response = array('status' => false,'message'=>'Profile data recieved.','data' => $Member);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Unauthorised, contact admin.');
            return response()->json($response, 401);
        }                
    }

    public function registerMember(Request $request){
        $validate=User::memberValidator($request);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Sponsor=User::where('username',$request->sponsor_code)->first();
        if(!$Sponsor){
            $response = array('status' => false,'message'=>'Sponsor not found.');
            return response()->json($response, 404);
        }

        $username=$this->generateMemberID();

        $User= User::create([
            'name' => $request->name,
            'username' => $username,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'contact'=>$request->contact,
            'gender'=>$request->gender,
            'dob'=>$request->dob,
            'is_active'=>1,
            'verified_at'=>Carbon::now()
        ]);

        $User->assignRole('user');

        $parent=$Sponsor->member->id;
        $parents=$this->getSponsorTail($parent,$request->position);
        if($parents){
            $parent=$parents[0]->id;
        }else{
            $parent=$Sponsor->member->id;
        }

        $Parent=Member::where('id',$parent)->first();
        $level=$Parent->level+1;
              
        $Member=new Member;
        $Member->user_id=$User->id;
        $Member->position=$request->position;
        $Member->sponsor_id=$Sponsor->member->id;
        $Member->parent_id=$Parent->id;
        
        $Member->level=$level;
        $Member->wallet_balance=0;
        $Member->save();

        $Member->path=$Parent->path.'/'.$Member->id;
        $Member->save();  

        $Kyc=new Kyc;
        $Kyc->member_id=$Member->id;
        $Kyc->verification_status='pending';
        $Kyc->save();

        
        $response = array('status' => true,'message'=>'You are registered successfully. Your ID is - '.$User->username);
        return response()->json($response, 200);
    }

    public function addMember(Request $request){
        $validate=User::memberValidator($request);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Parent=User::where('username',$request->parent_code)->first();
        if(!$Parent){
            $response = array('status' => false,'message'=>'Parent not found.');
            return response()->json($response, 404);
        }

        $Sponsor=User::where('username',$request->sponsor_code)->first();
        if(!$Sponsor){
            $response = array('status' => false,'message'=>'Sponsor not found.');
            return response()->json($response, 404);
        }

        $username=$this->generateMemberID();

        $User= User::create([
            'name' => $request->name,
            'username' => $username,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'contact'=>$request->contact,
            'gender'=>$request->gender,
            'dob'=>$request->dob,
            'is_active'=>1,
            'verified_at'=>Carbon::now()
        ]);

        $User->assignRole('user');

        // $parent=$Sponsor->member->id;
        // $parents=$this->getSponsorTail($parent,$request->position);
        // if($parents){
        //     $parent=$parents[0]->id;
        // }else{
        //     $parent=$Sponsor->member->id;
        // }

        //$Parent=Member::where('id',$parent)->first();
        $level=$Parent->member->level+1;
              
        $Member=new Member;
        $Member->user_id=$User->id;
        $Member->position=$request->position;
        $Member->sponsor_id=$Sponsor->member->id;
        $Member->parent_id=$Parent->member->id;
        
        $Member->level=$level;
        $Member->wallet_balance=0;
        $Member->save();

        $Member->path=$Parent->member->path.'/'.$Member->id;
        $Member->save();  

        $Kyc=new Kyc;
        $Kyc->member_id=$Member->id;
        $Kyc->verification_status='pending';
        $Kyc->save();

        
        $response = array('status' => true,'message'=>'Member added successfully. Member ID is - '.$User->username);
        return response()->json($response, 200);
    }  

    public function generateMemberID(){
        $member_id=mt_rand(100000, 999999);
        $user=User::where('username',$member_id)->first();
        if($user){
            $this->generateMemberID();
        }else{
            return $member_id;
        }
    }  

    public function getSponsorTail($parent,$position){
        $results = DB::select( DB::raw("select  id from    (select * from members where position=:position order by parent_id) positions_sorted, (select @pv := :parent) initialisation where   find_in_set(parent_id, @pv) and     length(@pv := concat(@pv, ',', id)) order by id desc limit 1"), 
            array(
                    'position' => $position,
                    'parent' => $parent,
            ));

        return $results;
    }

}
