<?php

use Illuminate\Database\Seeder;
use App\Models\Superadmin\ScheduledType;

class scheduledTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $schedule=[
           	['name' => 'Weekly'],
           	['name' => 'weeklyOn'],
           	['name' => 'Manual'],
           	['name' => 'monthly']
        ];


       	foreach ($schedule as $key=>$value){
        	ScheduledType::create($value);
      	}
    }
}
