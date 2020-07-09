<?php

use Illuminate\Database\Seeder;

use App\Models\Superadmin\PaymentMode;

class paymentModes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $payment=[
           [
               'name' => 'UPI',
               'is_active' => '1'
           ],
           [
               'name' => 'NEFT',
               'is_active' => '1'
           ],
           [
               'name' => 'IMPS',
               'is_active' => '0'
           ],
           [
               'name' => 'Cash',
               'is_active' => '0'
           ],
           [
               'name' => 'Paytm',
               'is_active' => '1'
           ],
           [
               'name' => 'Pin',
               'is_active' => '1'
           ],
           [
               'name' => 'Wallet',
               'is_active' => '1'
           ]
          
       ];

       	foreach ($payment as $key=>$value){
        	PaymentMode::create($value);
      	}
    }
}
