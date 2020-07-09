<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\Setting;
class settings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seting=[
           [
               'key' 			=> 'company_name',
               'value' 			=> 'Wyntash',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'company_about',
               'value' 			=> 'Gain Financial Freedom',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'tag_line',
               'value' 			=> 'We make your business Grow',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'website',
               'value' 			=> 'https://www.mlmworld.in',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'contact_email',
               'value' 			=> 'contact@vision4uedu.in',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'contact_phone',
               'value' 			=> '9724332304',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'support_email',
               'value' 			=> 'service@vision4uedu.in',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'support_phone',
               'value' 			=> '8000501652',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'address',
               'value' 			=> '609, Emporio',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'city',
               'value' 			=> 'Ahmedabad',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'state',
               'value' 			=> 'Gujarat',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'country',
               'value' 			=> 'India',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'pincode',
               'value' 			=> '382424',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'facebook_link',
               'value' 			=> 'facebook.com',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'youtube_link',
               'value' 			=> '#',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'twitter_link',
               'value' 			=> '#',
               'is_public' 		=> '1'
           ],
           [
               'key' 			=> 'instagram_link',
               'value' 			=> '#',
               'is_public' 		=> '1'
           ]
           
        ];


       	foreach ($seting as $key=>$value){
        	Setting::create($value);
      	}
    }
}
