<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\CompanySetting;

class companySettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company_seting=[
           [
               'key' => 'tds_percentage',
               'value' => '10'
           ],
           [
               'key' => 'minimum_purchase',
               'value' => '250'
           ],
           [
               'key' => 'is_automatic_payout',
               'value' => '0'
           ],
           [
               'key' => 'legs',
               'value' => '4'
           ],
           [
               'key' => 'admin_fee_percent',
               'value' => '3'
           ],
           [
               'key' => 'cashback_percent',
               'value' => '5'
           ]
       ];


       	foreach ($company_seting as $key=>$value){
        	CompanySetting::create($value);
      	}
    }
}
