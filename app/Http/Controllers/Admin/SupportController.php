<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User\Ticket;
use App\Models\User\TicketConversation;
use Storage;
Use JWTAuth;

class SupportController extends Controller
{    

   
    public function getMyOpened(Request $request)
    {
        $id=JWTAuth::user()->id;

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
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','opened');
            $Ticket = $Ticket->where('user_id',$id)->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','opened');
            
            $Ticket=$Ticket->where(function ($query)use($search) {
                $query->orWhere('subject','like','%'.$search.'%');
                $query->orWhere('id','like','%'.$search.'%');
            });

            $Ticket = $Ticket->where('user_id',$id)->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Tickets retrieved.",'data'=>$Ticket);
            return response()->json($response, 200);
    }
    
    public function getMyClosed(Request $request)
    {
        $id=JWTAuth::user()->id;

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
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','closed');
            $Ticket = $Ticket->where('user_id',$id)->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','closed');
            
            $Ticket=$Ticket->where(function ($query)use($search) {
                $query->orWhere('subject','like','%'.$search.'%');
                $query->orWhere('id','like','%'.$search.'%');
            });

            $Ticket = $Ticket->where('user_id',$id)->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Tickets retrieved.",'data'=>$Ticket);
            return response()->json($response, 200);
    }

    public function createTicket(Request $request)
    {   
        $id=JWTAuth::user()->id;

        $Ticket=new Ticket;
        $Ticket->subject=$request->subject;
        $Ticket->user_id=$id;
        $Ticket->status='opened';
        $Ticket->save();

        $TicketConversation=new TicketConversation;
        $TicketConversation->ticket_id=$Ticket->id;
        $TicketConversation->message=$request->message;
        $TicketConversation->from=$id;
        $TicketConversation->save();

        if($request->hasFile('attachment')){
            $file = $request->file('attachment');
            $str=rand(); 
            $randomID = md5($str);
            $filename=$randomID.'-'.$TicketConversation->id.".".$file->getClientOriginalExtension();          
            $project_directory=env('DO_STORE_PATH');

            $store=Storage::disk('spaces')->put($project_directory.'/ticket-attachments/'.$filename, file_get_contents($file->getRealPath()), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/ticket-attachments/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $TicketConversation->attachment=$cdn_url;
            $TicketConversation->save();
        }

        $Ticket=Ticket::with('conversations')->find($Ticket->id);

        $response = array('status' => false,'message'=>'Ticket created successfully.','data' => $Ticket);
        return response()->json($response, 200);
             
    }

    public function getConversations($ticket_id){
        $TicketConversation=TicketConversation::with(['from_user:id,name','to_user:id,name'])->where('ticket_id',$ticket_id)->orderBy('created_at','desc')->get();
        $response = array('status' => false,'message'=>'Ticket conversations received.','data' => $TicketConversation);
        return response()->json($response, 200);
    }

    public function addUserMessage(Request $request){
        $id=JWTAuth::user()->id;
        $Ticket= Ticket::where('user_id',$id)->where('id',$request->id)->first();
        
        if($Ticket){
           $TicketConversation=new TicketConversation;
            $TicketConversation->ticket_id=$request->id;
            $TicketConversation->message=$request->message;
            $TicketConversation->from=$id;
            $TicketConversation->to=$Ticket->assigned_to;
            $TicketConversation->save();

            if($request->hasFile('attachment')){
                $file = $request->file('attachment');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$TicketConversation->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/ticket-attachments/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/ticket-attachments/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $TicketConversation->attachment=$cdn_url;
                $TicketConversation->save();
            }

            $response = array('status' => true,'message'=>'Message sent.');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Ticket not found');
            return response()->json($response, 400);
        }
    }

    public function closeUserTicket(Request $request)
    {   
        $id=JWTAuth::user()->id;
        $Ticket= Ticket::where('user_id',$id)->where('id',$request->id)->first();
        if($Ticket){
            $Ticket->status='closed';
            $Ticket->save();
            $response = array('status' => true,'message'=>'Ticket closed successfully.');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Ticket not found');
            return response()->json($response, 400);
        }
            
    }

    public function getOpened(Request $request)
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
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','opened');
            $Ticket = $Ticket->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','opened');
            
            $Ticket=$Ticket->where(function ($query)use($search) {
                $query->orWhere('subject','like','%'.$search.'%');
                $query->orWhere('id','like','%'.$search.'%');

                $query->orWhereHas('user', function( $q ) use ( $search ){
                   $q->where('username','like','%'.$search.'%');
                });

                $query->orWhereHas('user', function( $q ) use ( $search ){
                   $q->where('name','like','%'.$search.'%');
                });                
            });

            $Ticket = $Ticket->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Tickets retrieved.",'data'=>$Ticket);
            return response()->json($response, 200);
    }
    
    public function getClosed(Request $request)
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
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','closed');
            $Ticket = $Ticket->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Ticket=Ticket::select();
            $Ticket=$Ticket->where('status','closed');
            
            $Ticket=$Ticket->where(function ($query)use($search) {
                $query->orWhere('subject','like','%'.$search.'%');
                $query->orWhere('id','like','%'.$search.'%');

                $query->orWhereHas('user', function( $q ) use ( $search ){
                   $q->where('username','like','%'.$search.'%');
                });

                $query->orWhereHas('user', function( $q ) use ( $search ){
                   $q->where('name','like','%'.$search.'%');
                });
                
            });

            $Ticket = $Ticket->with('conversations','assigned','user')->orderBy('id',$sort)->paginate($limit);
        }
   
       $response = array('status' => true,'message'=>"Tickets retrieved.",'data'=>$Ticket);
            return response()->json($response, 200);
    }

    public function addAdminMessage(Request $request){
        $id=JWTAuth::user()->id;
        $Ticket= Ticket::where('id',$request->id)->first();


        
        if($Ticket){
            
            if(!$Ticket->assigned_to){
                $Ticket->assigned_to=$id;
                $Ticket->save();        
            }

           $TicketConversation=new TicketConversation;
            $TicketConversation->ticket_id=$request->id;
            $TicketConversation->message=$request->message;
            $TicketConversation->from=$id;
            $TicketConversation->to=$Ticket->user_id;
            $TicketConversation->is_agent=1;
            $TicketConversation->save();

            if($request->hasFile('attachment')){
                $file = $request->file('attachment');
                $str=rand(); 
                $randomID = md5($str);
                $filename=$randomID.'-'.$TicketConversation->id.".".$file->getClientOriginalExtension();          
                $project_directory=env('DO_STORE_PATH');

                $store=Storage::disk('spaces')->put($project_directory.'/ticket-attachments/'.$filename, file_get_contents($file->getRealPath()), 'public');
                
                $url=Storage::disk('spaces')->url($project_directory.'/ticket-attachments/'.$filename);
                
                $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

                $TicketConversation->attachment=$cdn_url;
                $TicketConversation->save();
            }

            $response = array('status' => true,'message'=>'Message sent.');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Ticket not found');
            return response()->json($response, 400);
        }
    }

    public function closeTicket(Request $request)
    {   
        $Ticket= Ticket::where('id',$request->id)->first();
        if($Ticket){
            $Ticket->status='closed';
            $Ticket->save();
            $response = array('status' => true,'message'=>'Ticket closed successfully.');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Ticket not found');
            return response()->json($response, 400);
        }
            
    }

}
