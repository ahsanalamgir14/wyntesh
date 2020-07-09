<?php
use Illuminate\Database\Seeder;
use App\Models\Superadmin\Status;

class statuses extends Seeder
{
    public function run()
    {
        $status=[
           
           [
               'name' 			=> 'Order Created',
               'type' 			=> 'orders'
           ],
           [
               'name' 			=> 'Order Confirmed',
               'type' 			=> 'orders'
           ],
           [
               'name' 			=> 'Order Prepared',
               'type' 			=> 'orders'
           ],
           [
               'name' 			=> 'Order Dispached',
               'type' 			=> 'orders'
           ],
           [
               'name' 			=> 'Order Delivered',
               'type' 			=> 'orders'
           ],
           [
               'name' 			=> 'Order Cancelled',
               'type' 			=> 'orders'
           ],
           [
               'name' 			=> 'Used',
               'type' 			=> 'pins'
           ],
           [
               'name' 			=> 'Not Used',
               'type' 			=> 'pins'
           ],
           [
               'name' 			=> 'Pending',
               'type' 			=> 'pin_requests'
           ],
           [
               'name' 			=> 'Rejected',
               'type' 			=> 'pin_requests'
           ],
           [
               'name' 			=> 'Approved',
               'type' 			=> 'pin_requests'
           ],
           [
               'name' 			=> 'Success',
               'type' 			=> 'payments'
           ],
           [
               'name' 			=> 'Processing',
               'type' 			=> 'payments'
           ],
           [
               'name' 			=> 'Failed',
               'type' 			=> 'payments'
           ],
           [
               'name' 			=> 'Pending',
               'type' 			=> 'withdrawal_requests'
           ],
           [
               'name' 			=> 'Rejected',
               'type' 			=> 'withdrawal_requests'
           ],
           [
               'name' 			=> 'Approved',
               'type' 			=> 'withdrawal_requests'
           ],
           [
               'name' 			=> 'Paid',
               'type' 			=> 'withdrawals'
           ],
           [
               'name' 			=> 'In progress',
               'type' 			=> 'withdrawals'
           ],
           [
               'name' 			=> 'Pending',
               'type' 			=> 'credit_requests'
           ],
           [
               'name' 			=> 'Rejected',
               'type' 			=> 'credit_requests'
           ],
           [
               'name' 			=> 'Approved',
               'type' 			=> 'credit_requests'
           ],
           [
               'name' 			=> 'Order Returned',
               'type' 			=> 'orders'
           ],
           
        ];


       	foreach ($status as $key=>$value){
        	Status::create($value);
      	}
    }
}
