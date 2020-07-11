<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\User\Kyc;
use App\Models\Admin\Member;
use App\Models\Admin\Rank;
use App\Models\Admin\MembersLegPv;
use Illuminate\Support\Facades\Hash;
use Validator;
use Carbon\Carbon;
use JWTAuth;
use DB;
use Storage;
use App\Events\MemberRegisteredEvent;

class MembersController extends Controller
{    


    public function updateRank(){
        $Members=Member::orderBy('level','desc')->get();
        $Ranks=Rank::all();
        foreach ($Members as $Member) {
            $group_pv=MembersLegPv::where('member_id',$Member->id)->sum('total_pv');
            $children=Member::where('parent_id',$Member->id)->get()->pluck('id')->toArray();
            $counts=array();
            
            foreach ($children as $child) {
                $child_ids=$this->getChildsOfParent($child);
                $child_ids[]=$child;

               $check_rank=Member::whereIn('id',$child_ids)->get()->pluck('rank_id')->toArray();
               if($Member->id==1)
                
                $check_rank=array_unique($check_rank);
              foreach ($check_rank as $check) {
                        $counts[]=$check;
               }                           
            }
            
            $counts=array_count_values($counts);

            foreach ($Ranks as $Rank) {
               
                if($Rank->bv_to){
                    if($group_pv >= $Rank->bv_from ){
                       
                        $Member->rank_id=$Rank->id;
                        $Member->save();
                    }

                }else if($Rank->leg_rank){
                                     
                    foreach ($counts as $key => $value) {   
                        if($Rank->leg_rank===$key && $Rank->leg_rank_count == $value){                           
                            $Member->rank_id=$Rank->id;
                            $Member->save();   
                        }
                    }

                }

            } 
            
            
        }
    }

    public function getProfile()
    {   
        $id=JWTAuth::user()->id;

        $Member=User::with('kyc')->find($id);
        $response = array('status' => true,'message'=>'Profile data recieved.','data' => $Member);
        return response()->json($response, 200);
    }

    public function getAccuntStatus()
    {   
        $User=JWTAuth::user();
        $response = array('status' => true,'message'=>'Account status recieved.','is_active' => $User->is_active);
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
        $zero=Member::with('children.children')->with('kyc')->with('user')->with('rank')
        ->withCount(['leg_pv as group_pv' => function($query){
           $query->select(DB::raw('sum(pv)'));
        }])
        ->where('level',0)->first();
        $response = array('status' => false,'message'=>'Geneology recieved.','data' => $zero);
        return response()->json($response, 200);  
    }

    public function adminMemberGeneology($id){
        $User=User::where('username',$id)->first();
        if($User){
            $zero=Member::with('children.children')->with('kyc')->with('user')
            ->withCount(['leg_pv as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
            }])
            ->with('rank')->where('user_id',$User->id)->first();
            $response = array('status' => false,'message'=>'Geneology recieved.','data' => $zero);
            return response()->json($response, 200);  
        }else{
            $response = array('status' => false,'message'=>'Member not found');
            return response()->json($response, 404);
        }
        
    }

    public function myGeneology(){
        $id=JWTAuth::user()->id;
        $zero=Member::with('children.children')->with('kyc:id,member_id,verification_status,is_verified')->with('user')->with('rank')
            ->withCount(['leg_pv as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
            }])
            ->where('user_id',$id)->first();
        $response = array('status' => false,'message'=>'Geneology recieved.','data' => $zero);
        return response()->json($response, 200);  
    }

    public function getReferrals(Request $request)
    {
        $User=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $is_active=$request->is_active;
        $date_range=$request->date_range;
        
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

       
        if(!$search && !$date_range){
            $Members=Member::select();
            $Members=$Members->where('sponsor_id',$User->member->id);

            if($is_active!='all'){
                $Members=$Members->whereHas('user', function($q)use($is_active){
                    $q->where('is_active', $is_active);
                });
            }

            $Members=$Members->withCount(['leg_pv as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
            }]);

            $Members=$Members->with('parent:id,user_id','sponsor:id,user_id','user:id,username,name,is_active,is_blocked')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Members=Member::select();
            $Members=$Members->where('sponsor_id',$User->member->id);

            $Members=$Members->where(function ($query)use($search) {
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('name','like','%'.$search.'%');
                });
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('contact','like','%'.$search.'%');
                });
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('email','like','%'.$search.'%');
                });
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('username','like','%'.$search.'%');
                });
            });

            if($date_range){
                $Members=$Members->whereDate('created_at','>=', $date_range[0]);
                $Members=$Members->whereDate('created_at','<=', $date_range[1]);
            }

            if($is_active!='all'){
                $Members=$Members->whereHas('user', function($q)use($is_active){
                    $q->where('is_active', $is_active);
                });
            }

            $Members=$Members->withCount(['leg_pv as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
            }]);
       
            $Members=$Members->with('parent','sponsor','user')->orderBy('id',$sort)->paginate($limit);
            
        }  
       
       $response = array('status' => true,'message'=>"Members retrieved.",'data'=>$Members);
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
                $zero=Member::with('children.children')->with('kyc:id,member_id,verification_status,is_verified')
                ->with('user')->with('rank')
                ->withCount(['leg_pv as group_pv' => function($query){
                   $query->select(DB::raw('sum(pv)'));
                }])
                ->where('user_id',$User->id)->first();
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
            $User->kyc->nominee_name=$kyc['nominee_name'];
            $User->kyc->nominee_relation=$kyc['nominee_relation'];
            $User->kyc->nominee_dob=$kyc['nominee_dob'];
            $User->kyc->nominee_contact=$kyc['nominee_contact'];
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

            if($request->hasFile('adhar_image_back')){
                $file = $request->file('adhar_image_back');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$User->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $User->kyc->adhar_image_back=$cdn_url;
                $User->kyc->save();
            }

            if($request->hasFile('profile_picture')){
                $file = $request->file('profile_picture');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$User->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $User->profile_picture=$cdn_url;
                $User->save();
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

        $parent=$Sponsor->member->id;
        $parents=$this->getSponsorTail($parent,$request->position);
        if($parents){
            $parent=$parents[0]->id;
        }else{
            $parent=$Sponsor->member->id;
        }


        $Parent=Member::where('id',$parent)->first();

        $PositionEmptyCheck=Member::where('position',$request->position)->where('parent_id',$Parent->id)->first();
        if($PositionEmptyCheck){
            $response = array('status' => false,'message'=>'Position is already filled, try another position.');
            return response()->json($response, 400);
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
            'is_active'=>0,
            'verified_at'=>Carbon::now()
        ]);

        $User->assignRole('user');

        
        $level=$Parent->level+1;
              
        $Member=new Member;
        $Member->user_id=$User->id;
        $Member->position=$request->position;
        $Member->sponsor_id=$Sponsor->member->id;
        $Member->parent_id=$Parent->id;
        $Member->rank_id=1;
        $Member->level=$level;
        $Member->wallet_balance=0;
        $Member->save();

        $Member->path=$Parent->path.'/'.$Member->id;
        $Member->save();  

        $Kyc=new Kyc;
        $Kyc->member_id=$Member->id;
        $Kyc->verification_status='pending';
        $Kyc->save();

        event(new MemberRegisteredEvent($User));

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

        $PositionEmptyCheck=Member::where('position',$request->position)->where('parent_id',$Parent->member->id)->first();
        if($PositionEmptyCheck){
            $response = array('status' => false,'message'=>'Position is already filled, try another position.');
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
            'is_active'=>0,
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

        event(new MemberRegisteredEvent($User));
        
        $response = array('status' => true,'message'=>'Member added successfully. Member ID is - '.$User->username);
        return response()->json($response, 200);
    } 

    // Users
    public function getDownlines(Request $request)
    {
        $User=JWTAuth::user();
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $is_active=$request->is_active;
        $date_range=$request->date_range;
        
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

        $memberIds=$this->getChildsOfParent($User->member->id);

        if(!$search && !$date_range){
            $Members=Member::select();
            $Members=$Members->whereIn('id',$memberIds);

            if($is_active!='all'){
                $Members=$Members->whereHas('user', function($q)use($is_active){
                    $q->where('is_active', $is_active);
                });
            }

            $Members=$Members->withCount(['leg_pv as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
            }]);

            $Members=$Members->with('parent:id,user_id','sponsor:id,user_id','user:id,username,name,is_active,is_blocked')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Members=Member::select();
            $Members=$Members->whereIn('id',$memberIds);

            $Members=$Members->where(function ($query)use($search) {
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('name','like','%'.$search.'%');
                });
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('contact','like','%'.$search.'%');
                });
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('email','like','%'.$search.'%');
                });
                $query->orWhereHas('user', function($q)use($search){
                     $q->where('username','like','%'.$search.'%');
                });
            });

            $Members=$Members->withCount(['leg_pv as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
            }]);

            if($date_range){
                $Members=$Members->whereDate('created_at','>=', $date_range[0]);
                $Members=$Members->whereDate('created_at','<=', $date_range[1]);
            }

            if($is_active!='all'){
                $Members=$Members->whereHas('user', function($q)use($is_active){
                    $q->where('is_active', $is_active);
                });
            }
       
            $Members=$Members->with('parent','sponsor','user')->orderBy('id',$sort)->paginate($limit);
            
        }

        
       
       $response = array('status' => true,'message'=>"Members retrieved.",'data'=>$Members);
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

    public function getChildsOfParent($parent){
        $results = DB::select("select id from (select * from members order by parent_id, id) members, (select @pv := :parent) initialisation where find_in_set(parent_id, @pv) > 0 and @pv := concat(@pv, ',', id )", 
            array(
                    'parent' => $parent,
            ));
        $ids = array_column($results, 'id');

        return $ids;
    }

}
