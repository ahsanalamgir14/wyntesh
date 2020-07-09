<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\Notice as noticeClass;
class notice extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notice=[
           'title' => 'Member Notice',
           'is_active' => '1',
           'description' => 'Hi there submit KYC',
       	];


       	$Notice=noticeClass::create($notice);
    }
}
