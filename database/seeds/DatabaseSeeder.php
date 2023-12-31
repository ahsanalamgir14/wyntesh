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
       $files_arr = scandir( dirname(__FILE__) ); //store filenames into $files_array
       // dd($files_arr);
            foreach ($files_arr as $key => $file){
                if ($file !== 'DatabaseSeeder.php' && $file[0] !== "." ){
                $this->call(explode('.', $file)[0] );
                }
            }
    }
}
