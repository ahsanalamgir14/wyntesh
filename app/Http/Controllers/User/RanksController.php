<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Rank;
use App\Models\Admin\RankLog;
use JWTAuth;
use DB;
use Carbon\Carbon;

class RanksController extends Controller
{    

    
    public function getPayouts(Request $request)
    {
        $user=JWTAuth::user();
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
            $RankLog=RankLog::select();
            $RankLog=$RankLog->where('member_id',$user->member->id);
            $RankLog=$RankLog->with('payout:id,sales_start_date,sales_end_date')->orderBy('id',$sort)->paginate($limit);
        }else{
            $RankLog=RankLog::select();
            $RankLog=$RankLog->where('member_id',$user->member->id);
            $RankLog=$RankLog->with('payout:id,sales_start_date,sales_end_date')->orderBy('id',$sort)->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"RankLog Types retrieved.",'data'=>$RankLog);
        return response()->json($response, 200);
    }
    public function getRankLogs(Request $request)
    {
        $user=JWTAuth::user();
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
            $RankLog=RankLog::select();
            $RankLog=$RankLog->where('member_id',$user->member->id);
            $RankLog=$RankLog->with('rank')->orderBy('id','asc')->groupBy('rank_id')->paginate($limit);
        }else{
            $RankLog=RankLog::select();
            
            if($search){
                $RankLog=$RankLog->whereHas('rank',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });    
            }

            $RankLog=$RankLog->where('member_id',$user->member->id);
            $RankLog=$RankLog->with('rank')->orderBy('id','asc')->groupBy('rank_id')->paginate($limit);
        }
   
        $response = array('status' => true,'message'=>"Rank Logs retrieved.",'data'=>$RankLog);
        return response()->json($response, 200);
    }
}
