<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\PayoutType;


class payoutTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payout=[
           [
               'name' 			=> 'Weekly',
               'exection_type' 	=> 'weekly',
               'exection_day' 	=> '1',
               'exection_time' 	=> '01:30'
           ],
           [
               'name' 			=> 'Monthly',
               'exection_type' 	=> 'Monthly',
               'exection_day' 	=> '1',
               'exection_time' 	=> '01:00'
           ],
           [
               'name' 			=> 'Manual',
               'exection_type' 	=> 'Manual',
               'exection_day' 	=> '',
               'exection_time' 	=> ''
           ],
       ];


       	foreach ($payout as $key=>$value){
        	PayoutType::create($value);
      	}
    }
}
