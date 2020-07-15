<?php

use Illuminate\Database\Seeder;

use App\Models\Superadmin\TransactionType;
class TransactionTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transaction=[
           [
               	'name' 				=> 'Credit',
               	'is_active' 		=> 'Wyntash',
               	'created_at' 		=> date('Y-m-d')
           ],
           [
               	'name' 				=> 'Debit',
               	'is_active' 		=> '1',
               	'created_at' 		=> date('Y-m-d')
           ],
           [
               	'name' 				=> 'Withdrawal',
               	'is_active' 		=> '1',
               	'created_at' 		=> date('Y-m-d')
           ],

           [
               	'name' 				=> 'Balance Transfer',
               	'is_active' 		=> '1',
               	'created_at' 		=> date('Y-m-d')
           ],

           [
               	'name' 				=> 'Debit (Purchase)',
               	'is_active' 		=> '1',
               	'created_at' 		=> date('Y-m-d')
           ],

           [
               	'name' 				=> 'Matrix Income',
               	'is_active' 		=> '1',
               	'created_at' 		=> date('Y-m-d')
           ],
           [
                'name'        => 'Order Refund',
                'is_active'     => '1',
                'created_at'    => date('Y-m-d')
           ],      [
                'name'        => 'Debit (Admin)',
                'is_active'     => '1',
                'created_at'    => date('Y-m-d')
           ],

           
           [
                'name'        => 'Cashback Income',
                'is_active'     => '1',
                'created_at'    => date('Y-m-d')
           ],
           [
                'name'        => 'Cashback Refund',
                'is_active'     => '1',
                'created_at'    => date('Y-m-d')
           ],
        ];


       	foreach ($transaction as $key=>$value){
        	TransactionType::create($value);
      	}
    }
}
