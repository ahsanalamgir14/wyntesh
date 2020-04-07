<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\BankPartner;
use Storage;

class BankPartnersController extends Controller
{    

    //  get BankPartners
    public function getBankPartners(Request $request)
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
            $BankPartners = BankPartner::orderBy('id',$sort)->paginate($limit);    
        }else{
            $BankPartners=BankPartner::select();

            $BankPartners=$BankPartners->orWhere('name','like','%'.$search.'%');
            $BankPartners=$BankPartners->orWhere('branch_name','like','%'.$search.'%');
            $BankPartners=$BankPartners->orWhere('account_type','like','%'.$search.'%');
            $BankPartners=$BankPartners->orWhere('account_holder_name','like','%'.$search.'%');
            $BankPartners=$BankPartners->orWhere('account_number','like','%'.$search.'%');
            $BankPartners=$BankPartners->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"BankPartners retrieved.",'data'=>$BankPartners);
            return response()->json($response, 200);
    }
  

    public function createBankPartner(Request $request){
        $validate=BankPartner::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }


        $BankPartner=new BankPartner;
        $BankPartner->name=$request->name;
        $BankPartner->branch_name=$request->branch_name;
        $BankPartner->account_type=$request->account_type;
        $BankPartner->account_holder_name=$request->account_holder_name;
        $BankPartner->account_number=$request->account_number;
        $BankPartner->ifsc=$request->ifsc;
        $BankPartner->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$BankPartner->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/BankPartner/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/BankPartner/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $BankPartner->image=$cdn_url;
            $BankPartner->save();
        }
       
        $response = array('status' => true,'message'=>'Bank Partner created successfully.','data'=>$BankPartner);
        return response()->json($response, 200);
    }

    public function updateBankPartner(Request $request){        
        
        

        $BankPartner=BankPartner::find($request->id);
        
        if(!$BankPartner){
            $response = array('status' => false,'message'=>'Bank Partner not found');
            return response()->json($response, 404);
        }

        $BankPartner->name=$request->name;
        $BankPartner->branch_name=$request->branch_name;
        $BankPartner->account_type=$request->account_type;
        $BankPartner->account_holder_name=$request->account_holder_name;
        $BankPartner->account_number=$request->account_number;
        $BankPartner->ifsc=$request->ifsc; 
        $BankPartner->save();

        if($request->hasFile('file')){
            $file = $request->file('file');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$BankPartner->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/BankPartner/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/BankPartner/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $BankPartner->image=$cdn_url;
            $BankPartner->save();
        }

        $response = array('status' => true,'message'=>'Bank Partner updated successfully.','data'=>$BankPartner);
        return response()->json($response, 200);
    }


    public function deleteBankPartner($id){
        $BankPartner= BankPartner::find($id);         
        
         if($BankPartner){
            $BankPartner->delete(); 
            $response = array('status' => true,'message'=>'Bank Partner successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Bank Partner not found');
            return response()->json($response, 404);
        }
    }

}
