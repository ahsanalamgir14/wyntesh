<?php

namespace App\Imports;

use App\Models\User\User;
use App\Models\User\Kyc;
use App\Models\Admin\Member;
use Maatwebsite\Excel\Concerns\ToModel;
use Hash;
use Illuminate\Support\Facades\Log;
class UsersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {   

        $Sponsor=User::where('member_id',$row[9])->first();
        $Parent=User::where('member_id',$row[10])->first();
        
        if($Parent && $Sponsor){
            $count=Member::where('parent_id',$Parent->member->id)->count();
            $User= new User([
                'username'     => $row[3],
                'member_id'     => $row[1],
                'name'    => $row[2], 
                'contact'    => $row[6], 
                'email'    => $row[7], 
                'password' => '$2y$10$6BoG3J.FkDgjHOhUpzt6JOZ2OPdK4KIDqBDGDOJYiprW2h2X2dIFq',
            ]);

            $User->save();

            $User->assignRole('user');

            $member=new Member;

            $level=$Parent->member->level+1;
            
            $position=1;

            if($count){
                $position=$count+1;
            }else{
                $position=1;
            }

            $Member=new Member;
            $Member->user_id=$User->id;
            $Member->position=$position;
            $Member->sponsor_id=$Sponsor->member->id;
            $Member->parent_id=$Parent->member->id;
            
            $Member->level=$level;
            $Member->wallet_balance=0;
            $Member->save();

            $Member->path=$Parent->member->path.'/'.$Member->id;
            $Member->save();  

            $Kyc=new Kyc;
            $Kyc->member_id=$Member->id;
            $Kyc->verification_status='pending';
            $Kyc->save();

            return $User;
        }else{
            Log::info('User - '.$row[3]);
        }    
    }
}
