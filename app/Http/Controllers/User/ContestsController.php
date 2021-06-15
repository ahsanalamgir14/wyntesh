<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Contest;
use App\Models\Admin\ContestMember;
use App\Models\Admin\Member;
use Storage, Carbon\Carbon;

class ContestsController extends Controller
{    

    public function getContestStats(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $rank_id=$request->rank_id;

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

        
        $ContestMembers=ContestMember::select();

        if($search){
            $ContestMembers=$ContestMembers->whereHas('member.user',function($q)use($search){
                $q->where('username',$search);
            });
        }

        $ContestMembers=$ContestMembers->where('rank_id',$rank_id);
        $ContestMembers=$ContestMembers->orderBy('points','desc')->paginate($limit);
        
        $response = array('status' => true,'message'=>"Contest members retrieved.",'data'=>$ContestMembers);
        return response()->json($response, 200);
    }

    public function getCurrentContest(){
        $contest=Contest::where('is_current',1)->first();
        
        if(!$contest){
            $response = array('status' => false,'message'=>"Contest not found.");
            return response()->json($response, 404);
        }

        $response = array('status' => true,'message'=>"Contest retrieved.",'data'=>$contest);
        return response()->json($response, 200);
    }
  
}
