<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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
           
               'name' => 'Super Admin',
               'username' => 'superadmin',
               'email' => 'sadmin@admin.com',
               'contact' => '9724332304',
               'password' => bcrypt('123456')     
       	];
       	
       	foreach ($permissions as $key=>$value){
        	Permission::create($value);
      	}

       	foreach ($roles as $key=>$value){
        	Role::create($value);
      	}

       	$user=User::create($user);
       	$user->assignRole('superadmin');
    }
}
