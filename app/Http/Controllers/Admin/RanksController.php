<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\Rank;
use App\Models\Admin\RankLog;

class RanksController extends Controller
{    

    //  get Ranks
    public function getRanks(Request $request)
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
            $Ranks = Rank::with('legRank')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Ranks=Rank::select();

            $Ranks=$Ranks->orWhere('name','like','%'.$search.'%');
            $Ranks=$Ranks->with('legRank')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Ranks retrieved.",'data'=>$Ranks);
            return response()->json($response, 200);
    }

    public function getRankLogs(Request $request)
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
        
        $RankLogs=RankLog::select();

        if($search){
            $RankLogs=$RankLogs->where(function ($query)use($search) {          
                $query=$query->orWhereHas('rank',function($q)use($search){
                    $q->where('name','like','%'.$search.'%');
                });
                $query=$query->orWhereHas('member.user',function($q)use($search){
                    $q->where('username','like','%'.$search.'%');
                });
            });  
        }
        
        $RankLogs=$RankLogs->with('rank','member.user')->groupBy('member_id')->groupBy('rank_id')->orderBy('id',$sort)->paginate($limit);
        $response = array('status' => true,'message'=>"Rank Logs retrieved.",'data'=>$RankLogs);
        return response()->json($response, 200);
    }
    
    public function getAllRanks(Request $request)
    {
        $Ranks=Rank::all();
        $response = array('status' => true,'message'=>'Ranks retrieved.','data'=>$Ranks);
        return response()->json($response, 200);
    }

    public function createRank(Request $request){
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'capping' => 'required|numeric',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $Rank=new Rank;
        $Rank->name=$request->name;
        $Rank->capping=$request->capping;
        $Rank->bv_from=$request->bv_from;
        $Rank->bv_to=$request->bv_to;
        $Rank->leg_rank=$request->leg_rank;
        $Rank->leg_rank_count=$request->leg_rank_count;
        $Rank->personal_bv_condition=$request->personal_bv_condition;
        $Rank->save();

        $response = array('status' => true,'message'=>'Rank created successfully.','data'=>$Rank);
        return response()->json($response, 200);
    }

    public function updateRank(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'capping' => 'required|numeric',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $Rank=Rank::find($request->id);
        $Rank->name=$request->name;
        $Rank->capping=$request->capping;
        $Rank->bv_from=$request->bv_from;
        $Rank->bv_to=$request->bv_to;
        $Rank->leg_rank=$request->leg_rank;
        $Rank->leg_rank_count=$request->leg_rank_count;
        $Rank->personal_bv_condition=$request->personal_bv_condition;
        $Rank->save();


        $response = array('status' => true,'message'=>'Rank updated successfully.','data'=>$Rank);
        return response()->json($response, 200);
    }

    public function deleteRank($id){
        $Rank= Rank::find($id);         
        
         if($Rank){
            $Rank->delete(); 
            $response = array('status' => true,'message'=>'Rank successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Rank not found','data' => array());
            return response()->json($response, 404);
        }
    }

}
