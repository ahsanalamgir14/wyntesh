<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Superadmin\Status;
use Storage;

class StatusesController extends Controller
{    

    //  get Statuses
    public function getStatuses(Request $request)
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
            $Statuses = Status::orderBy('id',$sort)->paginate($limit);    
        }else{
            $Statuses=Status::select();

            $Statuses=$Statuses->orWhere('name','like','%'.$search.'%');
            $Statuses=$Statuses->orWhere('type','like','%'.$search.'%');
            $Statuses=$Statuses->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Statuses retrieved.",'data'=>$Statuses);
            return response()->json($response, 200);
    }

    public function getAllStatuses(Request $request)
    {
        $type=$request->type;
        $Statuses=Status::select();
        
        if($type){
            $Statuses=$Statuses->where('type',$type);
        }

        $Statuses=$Statuses->get();

       $response = array('status' => true,'message'=>"Statuses retrieved.",'data'=>$Statuses);
            return response()->json($response, 200);
    }
  

    public function createStatus(Request $request){
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $Status=new Status;
        $Status->name=$request->name;
        $Status->type=$request->type;
        $Status->save();

        $response = array('status' => true,'message'=>'Status created successfully.','data'=>$Status);
        return response()->json($response, 200);
    }

    public function updateStatus(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        
        $Status=Status::find($request->id);
        $Status->name=$request->name;
        $Status->type=$request->type;
        $Status->save();


        $response = array('status' => true,'message'=>'Status updated successfully.','data'=>$Status);
        return response()->json($response, 200);
    }

    public function deleteStatus($id){
        $Status= Status::find($id);         
        
         if($Status){
            $Status->delete(); 
            $response = array('status' => true,'message'=>'Status successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Status not found');
            return response()->json($response, 404);
        }
    }

}
