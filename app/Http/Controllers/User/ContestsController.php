<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Contest;
use App\Models\Admin\ContestMember;
use App\Models\Admin\ContestReward;
use App\Models\Admin\ContestBanner;
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

        $contest=Contest::where('is_current',1)->first();
        
        if(!$contest){
            $response = array('status' => true,'message'=>"Contest retrieved.",'data'=>null);
            return response()->json($response, 200);
        }
        
        $ContestMembers=ContestMember::select();

        if($search){
            $ContestMembers=$ContestMembers->whereHas('member.user',function($q)use($search){
                $q->where('username',$search);
            });
        }

        if($rank_id >1 && $rank_id <=4){
            $ContestMembers=$ContestMembers->where('contest_id',$contest->id)->where('member_id','!=',3)->where('rank_id','>=',$rank_id);
        }else{
            $ContestMembers=$ContestMembers->where('contest_id',$contest->id)->where('rank_id',$rank_id);            
        }

        $ContestMembers=$ContestMembers->with('member.user','member.kyc')->where('points','!=',0)->orderBy('points','desc')->paginate($limit);
        
        $response = array('status' => true,'message'=>"Contest members retrieved.",'data'=>$ContestMembers);
        return response()->json($response, 200);
    }

    public function getSpecialAwards(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

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
        
        $ContestRewards=ContestReward::select();

        if($search){
            $ContestRewards=$ContestRewards->whereHas('member.user',function($q)use($search){
                $q->where('username',$search);
            });
        }
        
        $ContestRewards=$ContestRewards->with('member.user')->paginate($limit);
        
        $response = array('status' => true,'message'=>"Contest reward members retrieved.",'data'=>$ContestRewards);
        return response()->json($response, 200);
    }

    public function getCurrentContest(){
        $contest=Contest::where('is_current',1)->first();
        
        if(!$contest){
            $response = array('status' => true,'message'=>"Contest retrieved.",'data'=>null);
            return response()->json($response, 200);
        }

        $response = array('status' => true,'message'=>"Contest retrieved.",'data'=>$contest);
        return response()->json($response, 200);
    }

    public function getCurrentContestRankBanner(Request $request){
        $contest=Contest::where('is_current',1)->first();
        
        if(!$contest){
            $response = array('status' => true,'message'=>"Contest retrieved.",'data'=>null);
            return response()->json($response, 200);
        }
        $contestBanner=null;
        if($contest->is_ended)
            $contestBanner=ContestBanner::where('rank_id',$request->rank_id)->where('contest_id',$contest->id)->first();

        $response = array('status' => true,'message'=>"Contest banner retrieved.",'data'=>$contestBanner);
        return response()->json($response, 200);
    }
  
}
