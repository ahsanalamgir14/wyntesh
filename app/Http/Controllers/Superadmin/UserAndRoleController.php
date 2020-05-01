<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Validator;
use Carbon\Carbon;
use DB;

class UserAndRoleController extends Controller
{    

   
    public function changeUserStatus(Request $request){
        $User=User::find($request->id);

        if($User){
            $User->is_active=$request->is_active;
            $User->save();
            $response = array('status' => true,'message'=>'User status changed successfully');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'User not found');
            return response()->json($response, 400);
        }
    }

    public function getAdminUsers(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;
        $is_active=$request->is_active;

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
            $users = User::role(['admin','superadmin']);

            if($is_active!='all'){
                $users=$users->where('is_active',$is_active);    
            }

            $users=$users->with('roles')->with('permissions')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $users=User::select();

            $users=$users->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%')
                ->orWhere('contact','like','%'.$search.'%')
                ->orWhere('email','like','%'.$search.'%');
            });

            if($is_active!='all'){
                $users=$users->where('is_active',$is_active);    
            }
            
            $users = $users->role(['admin','superadmin']);

            $users=$users->with('roles')->with('permissions')->orderBy('id',$sort)->paginate($limit);
        }

        
       
       $response = array('status' => true,'message'=>"users retrieved.",'data'=>$users);
            return response()->json($response, 200);
    }
        

    public function createAdminUser(Request $request){
        $validate=User::validator($request);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $verified_at=Carbon::now();

        $User= User::create([
            'name' => $request->name,
            'username'=>$request->username,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'contact'=>$request->contact,
            'gender'=>$request->gender,
            'dob'=>$request->dob,
            'is_active'=>1,
            'verified_at'=>$verified_at
        ]);

        if($request->roles){                
            $User->syncRoles($request->roles);
        }

        if($request->permissions){                
            $User->syncPermissions($request->permissions);
        }
        
        $User=User::with('roles')->with('permissions')->find($User->id);
        $response = array('status' => true,'message'=>'User created successfully.','data'=>$User);
        return response()->json($response, 200);
    }

    public function updateAdminUser(Request $request){        
        
        $exist=User::whereNotIn('id',[$request->id])->where('username',[$request->username])->first();

        if($exist){
            $response = array('status' => false,'message'=>'Username already exists.');
            return response()->json($response, 400);
        }

        $validate=User::updateValidator($request);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $User= User::find($request->id);
        if($User){
            $User->name=$request->name;
            $User->contact=$request->contact;
            $User->gender=$request->gender;
            $User->dob=$request->dob;
            

            if($request->password){
                $User->password=Hash::make($request->password);
            }

            $User->save();

            if($request->roles){                
                $User->syncRoles($request->roles);
            }

            if($request->permissions){                
                $User->syncPermissions($request->permissions);
            }
      
            $User=User::with('roles')->with('permissions')->find($User->id);
            $response = array('status' => true,'message'=>'User updated successfully.','data'=>$User);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'User not found.');
            return response()->json($response, 404);
            }            
    }




    // Roles
    public function getRoles(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){
            $roles = Role::select();

            $roles=$roles->orderBy('id',$sort)->paginate($limit);    
        }else{
            $roles=Role::select();
            
            $roles=$roles->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');
            });

            $roles=$roles->orderBy('id',$sort)->paginate($limit);
        }        
       
        $response = array('status' => true,'message'=>"roles retrieved.",'data'=>$roles);
            return response()->json($response, 200);
    }

    public function createRole(Request $request){
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){            
             $response = array('status' => false,'message'=>"Validation error.",'data'=>$validate->messages());
            return response()->json($messages, 400);
        }

        $Role= Role::create([
            'name' => $request->name
        ]);

        $response = array('status' => true,'message'=>'Role created successfully.','data'=>$Role);
        return response()->json($response, 200);
    }

    public function updateRole(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'id' => 'required'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $exist=Role::whereNotIn('id',[$request->id])->where('name',[$request->name])->first();

        if($exist){
            $response = array('status' => false,'message'=>'Role name already exists.');
            return response()->json($response, 400);
        }
        
        $Role= Role::find($request->id);
        if($Role){
            $Role->name=$request->name;
            $Role->save();
            $response = array('status' => true,'message'=>'Role updated successfully.','data'=>$Role);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'role not found.');             
            return response()->json($response, 404);
        }            
    }

    public function getRole($id){
        $Role= Role::find($id);  
         if($Role){
            $response = array('status' => 'success','data'=>$Role);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'role not found.');             
            return response()->json($response, 404);
        }
    }

    public function deleteRole($id){        
        $Role= Role::whereId($id)->delete(); 
         if($Role){
            $response = array('status' => 'success','message'=>'Role successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'role not found.');             
            return response()->json($response, 404);
        }
    }

    //Permissions
    public function getPermissions(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){
            $Permissions = Permission::select();

            $Permissions=$Permissions->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Permissions=Permission::select();
            $Permissions=$Permissions->where(function ($query)use($search) {
                $query->orWhere('name','like','%'.$search.'%');
            });
            $Permissions=$Permissions->orderBy('id',$sort)->paginate($limit);
        }        
       
        $response = array('status' => true,'message'=>"Permissions retrieved.",'data'=>$Permissions);
            return response()->json($response, 200);
    }

    public function createPermission(Request $request){
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){            
             $response = array('status' => false,'message'=>"Validation error.",'data'=>$validate->messages());
            return response()->json($messages, 400);
        }

        $Permission= Permission::create([
            'name' => $request->name
        ]);

        $response = array('status' => true,'message'=>'Permission created successfully.','data'=>$Permission);
        return response()->json($response, 200);
    }

    public function updatePermission(Request $request){        
        
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'id' => 'required'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $exist=Permission::whereNotIn('id',[$request->id])->where('name',[$request->name])->first();

        if($exist){
            $response = array('status' => false,'message'=>'Permission name already exists.');
            return response()->json($response, 400);
        }
        
        $Permission= Permission::find($request->id);
        if($Permission){
            $Permission->name=$request->name;
            $Permission->save();
            $response = array('status' => true,'message'=>'Permission updated successfully.','data'=>$Permission);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Permission not found.');             
            return response()->json($response, 404);
        }            
    }

    public function getPermission($id){
        $Permission= Permission::find($id);  
         if($Permission){
            $response = array('status' => 'success','data'=>$Permission);             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Permission not found.');             
            return response()->json($response, 404);
        }
    }

    public function deletePermission($id){        
        $Permission= Permission::whereId($id)->delete(); 
         if($Permission){
            $response = array('status' => 'success','message'=>'Permission successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Permission not found.');             
            return response()->json($response, 404);
        }
    }

}
