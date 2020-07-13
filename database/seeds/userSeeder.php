<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User\User;
use App\Models\User\Kyc;
use App\Models\Admin\Member;
class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $permissions=[
           [
               'name' => 'role-read'
           ],
       ];

       $roles=[
           
           [
               'name' => 'superadmin'
           ],
           [
               'name' => 'admin'
           ]
           ,
           [
               'name' => 'user'
           ]
       ];

       	

    	$user=[
       
            [
               	'name' => 'Super Admin',
               	'username' => 'superadmin',
               	'email' => 'sadmin@admin.com',
               	'contact' => '9724332304',
               	'password' => bcrypt('123456'),
               	'verified_at'=>date("y-m-d h:i:s")
       		],
       		[
               	'name' => 'Admin',
               	'username' => 'admin',
               	'email' => 'admin@admin.com',
               	'contact' => '8000501652',
               	'password' => bcrypt('123456'),
               	'verified_at'=>date("y-m-d h:i:s")
       		],
            [
               	'name' => 'Main ID',
               	'username' => '111111',
               	'email' => 'service@mlmworld.in',
               	'contact' => '9099701652',
               	'password' => bcrypt('123456'),
                'is_active' => 1,
               	'verified_at'=>date("y-m-d h:i:s")
       		],
       	];

    	$members=[
       		'user_id' => 3,
           	'position' => '',
           	'sponsor_id' => '',
           	'parent_id' => '',
           	'path' => '/1',
           	'wallet_balance' => 100000,
           	'current_personal_pv' => '0.00',
           	'total_personal_pv' => '0.00',
           	'total_matched_bv' => 0,
           	'rank_id' => 1,
           	'level' => 0
       	];
    	
    	$kyc=[
       		'member_id' => 1,
          'verification_status' => "pending",
        ];
       	
       	foreach ($permissions as $key=>$value){
        	Permission::create($value);
      	}

       	foreach ($roles as $key=>$value){
        	Role::create($value);
      	}

       	foreach ($user as $key=>$value){
        	$users = User::create($value);
       		if($key == 0){
       			$users->assignRole('superadmin');
       		}else if($key == 1){
				$users->assignRole('admin');
       		}else{
       			$users->assignRole('user');
       		}
      	}

        Member::create($members);
        Kyc::create($kyc);

    }
}
