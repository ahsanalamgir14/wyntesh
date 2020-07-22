<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User\Kyc;
use Storage;

class KycController extends Controller
{    

    
    public function getPendingKyc(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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

        if(!$search){
            $Kyc=Kyc::select();
            $Kyc=$Kyc->where('verification_status','submitted');
            $Kyc = $Kyc->with('member.user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Kyc=Kyc::select();
            $Kyc=$Kyc->where('verification_status','submitted');
            
            $Kyc=$Kyc->where(function ($query)use($search) {
                $query->orWhere('pan','like','%'.$search.'%');
                $query->orWhere('adhar','like','%'.$search.'%');
                $query->orWhereHas('member.user', function( $q ) use ( $search ){
                  $q->where('username', 'like','%'.$search.'%' );
                });

            });

            $Kyc=$Kyc->with('member.user')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Kycs retrieved.",'data'=>$Kyc);
            return response()->json($response, 200);
    }

    public function getRejectedKyc(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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

        if(!$search){
            $Kyc=Kyc::select();
            $Kyc=$Kyc->where('verification_status','rejected');
            $Kyc = $Kyc->with('member.user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Kyc=Kyc::select();
            $Kyc=$Kyc->where('verification_status','rejected');
            
            $Kyc=$Kyc->where(function ($query)use($search) {
                $query->orWhere('pan','like','%'.$search.'%');
                $query->orWhere('adhar','like','%'.$search.'%');
                $query->orWhereHas('member.user', function( $q ) use ( $search ){
                  $q->where('username', 'like','%'.$search.'%' );
                });

            });

            $Kyc=$Kyc->with('member.user')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Kycs retrieved.",'data'=>$Kyc);
            return response()->json($response, 200);
    }

    public function getVerifiedKyc(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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

        if(!$search){
            $Kyc=Kyc::select();
            $Kyc=$Kyc->where('verification_status','verified');
            $Kyc = $Kyc->with('member.user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Kyc=Kyc::select();
            $Kyc=$Kyc->where('verification_status','verified');
            
            $Kyc=$Kyc->where(function ($query)use($search) {
                $query->orWhere('pan','like','%'.$search.'%');
                $query->orWhere('adhar','like','%'.$search.'%');
                $query->orWhereHas('member.user', function( $q ) use ( $search ){
                  $q->where('username', 'like','%'.$search.'%' );
                });

            });

            $Kyc=$Kyc->with('member.user')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Kycs retrieved.",'data'=>$Kyc);
            return response()->json($response, 200);
    }
    
    public function updateMemberKyc(Request $request)
    {   
       
        $Kyc=Kyc::find($request->id);
        
        if($Kyc){
            
            $Kyc->address=$request->address;
            $Kyc->adhar=$request->adhar;
            $Kyc->pincode=$request->pincode;
            $Kyc->pan=$request->pan;
            $Kyc->city=$request->city;
            $Kyc->state=$request->state;
            $Kyc->bank_ac_name=$request->bank_ac_name;
            $Kyc->bank_name=$request->bank_name;
            $Kyc->bank_ac_no=$request->bank_ac_no;
            $Kyc->ifsc=$request->ifsc;
            $Kyc->nominee_name=$request->nominee_name;
            $Kyc->nominee_relation=$request->nominee_relation;
            $Kyc->nominee_dob=$request->nominee_dob;
            $Kyc->nominee_contact=$request->nominee_contact;
            $Kyc->remarks=$request->remarks;
            $Kyc->verification_status=$request->verification_status;
            if($request->verification_status=='verified'){
                $Kyc->is_verified=1;
            }else{
                $Kyc->is_verified=0;
            }
            $Kyc->save();

            if($request->hasFile('adhar_image')){
                $file = $request->file('adhar_image');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$Kyc->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $Kyc->adhar_image=$cdn_url;
                $Kyc->save();
            }

            if($request->hasFile('adhar_image_back')){
                $file = $request->file('adhar_image_back');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$Kyc->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $Kyc->adhar_image_back=$cdn_url;
                $Kyc->save();
            }

            if($request->hasFile('pan_image')){
                $file = $request->file('pan_image');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$Kyc->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $Kyc->pan_image=$cdn_url;
                $Kyc->save();
            }

            if($request->hasFile('cheque_image')){
                $file = $request->file('cheque_image');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$Kyc->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $Kyc->cheque_image=$cdn_url;
                $Kyc->save();
            }
            if($request->hasFile('distributor_image')){
                $file = $request->file('distributor_image');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$Kyc->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/kyc/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/kyc/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $Kyc->distributor_image=$cdn_url;
                $Kyc->save();
            }

         

            
            $Kyc= Kyc::with('member.user')->find($request->id);
            $response = array('status' => false,'message'=>'Kyc details updated.','data' => $Kyc);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Kyc details not found.');
            return response()->json($response, 404);
        }                
    }

}
