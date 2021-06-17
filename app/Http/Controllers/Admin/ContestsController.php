<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Contest;
use App\Models\Admin\ContestMember;
use App\Models\Admin\ContestReward;
use App\Models\Admin\ContestBanner;
use App\Models\Admin\Member;
use App\Models\User\User;
use Storage, Carbon\Carbon;

class ContestsController extends Controller
{    

    //  get Contests
    
    public function getAllContests(){
        $contests=Contest::orderBy('id','desc')->get();
        $response = array('status' => true,'message'=>"Contests retrieved.",'data'=>$contests);
        return response()->json($response, 200);
    }

    public function getContests(Request $request)
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

        
        $Contests=Contest::select();

        if($search){
            $Contests=$Contests->orWhere('name','like','%'.$search.'%');
        }
        
        $Contests=$Contests->orderBy('id',$sort)->paginate($limit);
        
        $response = array('status' => true,'message'=>"Contests retrieved.",'data'=>$Contests);
        return response()->json($response, 200);
    }
  

    public function createContest(Request $request){
        $validate = Validator::make($request->all(), [           
            'name' => "required",
            'start_date' => "required|date",
            'end_date' => "required|date",
            'number_of_winners' => "required|integer",
        ]);

        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Contest=new Contest;
        $Contest->name=$request->name;
        $Contest->description=$request->description;
        $Contest->start_date=$request->start_date;
        $Contest->end_date=$request->end_date;
        $Contest->number_of_winners=$request->number_of_winners;
        $Contest->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Contest->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/Contest/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/Contest/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Contest->image=$cdn_url;
            $Contest->save();
        }
       
        $response = array('status' => true,'message'=>'Contest created successfully.','data'=>$Contest);
        return response()->json($response, 200);
    }

    public function updateContest(Request $request){        
        
        $validate = Validator::make($request->all(), [           
            'name' => "required",
            'start_date' => "required|date",
            'end_date' => "required|date",
            'number_of_winners' => "required|integer",
        ]);

        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Contest=Contest::find($request->id);
        $Contest->name=$request->name;
        $Contest->description=$request->description;
        $Contest->start_date=$request->start_date;
        $Contest->end_date=$request->end_date;
        $Contest->number_of_winners=$request->number_of_winners;
        $Contest->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Contest->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/Contest/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/Contest/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $Contest->image=$cdn_url;
            $Contest->save();
        }

        $response = array('status' => true,'message'=>'Contest updated successfully.','data'=>$Contest);
        return response()->json($response, 200);
    }

    public function startContest($id){
        $Contest= Contest::find($id);         
        
        if(!$Contest){
            $response = array('status' => false,'message'=>'Contest not found');
            return response()->json($response, 404);
        }

        $contestMemberExists=ContestMember::where('contest_id',$Contest->id)->exists();

        if($contestMemberExists){
            $response = array('status' => false,'message'=>'Contest is already started or completed.');
            return response()->json($response, 400);    
        }

        $today=Carbon::today();

        if($today->gt($Contest->end_date)){
            $response = array('status' => false,'message'=>'Contest end date is already passed.');
            return response()->json($response, 400);    
        }

        $members=Member::all();

        foreach ($members as $member) {
            $ContestMember=new ContestMember;
            $ContestMember->contest_id=$Contest->id;
            $ContestMember->member_id=$member->id;
            $ContestMember->rank_id=$member->rank_id;
            $ContestMember->save();
        }

        $updateContest=Contest::where('id','!=',$id)->update([
            'is_current'=>0
        ]);

        $updateContest=Contest::where('id',$id)->update([
            'is_current'=>1
        ]);

        $response = array('status' => true,'message'=>'Contest started.');             
        return response()->json($response, 200);
        
    }

    public function deleteContest($id){
        $Contest= Contest::find($id);         
        
        if(!$Contest){
            $response = array('status' => false,'message'=>'Contest not found');
            return response()->json($response, 404);
        }

        $today=Carbon::today();

        if($today->gt($Contest->start_date)){
            $response = array('status' => false,'message'=>'Contest is already passed or started, cannot delete.');
            return response()->json($response, 400);    
        }

        $Contest->delete(); 
        $response = array('status' => true,'message'=>'Contest successfully deleted.');             
        return response()->json($response, 200);
    }

    public function getContestBanners(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $contest_id=$request->contest_id;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=10;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        
        $ContestBanner=ContestBanner::select();
        $ContestBanner=$ContestBanner->where('contest_id',$contest_id);        
        $ContestBanner=$ContestBanner->with('rank')->orderBy('id',$sort)->paginate($limit);
        
        $response = array('status' => true,'message'=>"Contest Banner retrieved.",'data'=>$ContestBanner);
        return response()->json($response, 200);
    }
  

    public function createContestBanner(Request $request){
        $validate = Validator::make($request->all(), [           
            'rank_id' => "required",
            'contest_id' => "required",
        ]);

        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Contest=Contest::find($request->contest_id);

        if(!$Contest){
            $response = array('status' => false,'message'=>'Contest not found');
            return response()->json($response, 404);
        }

        $ContestBanner=new ContestBanner;
        $ContestBanner->rank_id=$request->rank_id;
        $ContestBanner->contest_id=$request->contest_id;
        $ContestBanner->save();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$Contest->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/Contest/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/Contest/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $ContestBanner->image=$cdn_url;
            $ContestBanner->save();
        }
       
        $response = array('status' => true,'message'=>'Contest popup added successfully.','data'=>$Contest);
        return response()->json($response, 200);
    }

    public function getContestRewards(Request $request){

        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $contest_id=$request->contest_id;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=100;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        $contestRewards=ContestReward::select();

        $contestRewards=$contestRewards->where('contest_id',$contest_id);

        $contestRewards=$contestRewards->with('member.user')->orderBy('id','desc')->paginate($limit);

        $response = array('status' => true,'message'=>"Contests rewards retrieved.",'data'=>$contestRewards);
        return response()->json($response, 200);
    }

    public function createSpecialReward(Request $request){        
        
        $validate = Validator::make($request->all(), [           
            'title' => "required",
            'member_id' => "required",
            'contest_id' => "required",
        ]);

        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $Contest=Contest::find($request->contest_id);

        if(!$Contest){
            $response = array('status' => false,'message'=>'Contest not found');
            return response()->json($response, 404);
        }

        $member=Member::whereHas('user',function($q)use($request){
            $q->where('username',$request->member_id);
        })->first();

        if(!$member){
            $response = array('status' => false,'message'=>'member not found');
            return response()->json($response, 404);   
        }

        $ContestReward=new ContestReward;
        $ContestReward->title=$request->title;
        $ContestReward->member_id=$member->id;
        $ContestReward->contest_id=$request->contest_id;
        $ContestReward->save();

        $response = array('status' => true,'message'=>'Contest Award added successfully.','data'=>$Contest);
        return response()->json($response, 200);
    }

    public function updateSpecialReward(Request $request){        
        
        $validate = Validator::make($request->all(), [           
            'title' => "required",
            'member_id' => "required",
            'id' => "required",
        ]);

        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $ContestReward=ContestReward::find($request->id);

        if(!$ContestReward){
            $response = array('status' => false,'message'=>'Contest Award not found');
            return response()->json($response, 404);
        }

        $member=Member::whereHas('user',function($q)use($request){
            $q->where('username',$request->member_id);
        })->first();

        if(!$member){
            $response = array('status' => false,'message'=>'member not found');
            return response()->json($response, 404);   
        }

        $ContestReward->title=$request->title;
        $ContestReward->member_id=$member->id;
        $ContestReward->save();

        $response = array('status' => true,'message'=>'Contest Award added successfully.','data'=>$ContestReward);
        return response()->json($response, 200);
    }

    public function deleteContestSpecialReward($id){
        $ContestReward= ContestReward::find($id);         
        
        if(!$ContestReward){
            $response = array('status' => false,'message'=>'Contest Award not found');
            return response()->json($response, 404);
        }

        $ContestReward->delete(); 
        $response = array('status' => true,'message'=>'Contest Award successfully deleted.');             
        return response()->json($response, 200);
    }

    public function deleteContestBanner($id){
        $ContestBanner= ContestBanner::find($id);         
        
        if(!$ContestBanner){
            $response = array('status' => false,'message'=>'Contest popup not found');
            return response()->json($response, 404);
        }

        $ContestBanner->delete(); 
        $response = array('status' => true,'message'=>'Contest popup successfully deleted.');             
        return response()->json($response, 200);
    }

}
