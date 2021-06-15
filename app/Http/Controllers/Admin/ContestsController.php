<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Contest;
use App\Models\Admin\ContestMember;
use App\Models\Admin\Member;
use Storage, Carbon\Carbon;

class ContestsController extends Controller
{    

    //  get Contests
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

}
