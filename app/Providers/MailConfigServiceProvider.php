<?php

namespace App\Providers;

use Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // $settings= DB::table('settings')->get()->pluck('value', 'key')->toArray();
        // $mail=Config::get('mail');
        // if(isset($settings['mailgun_domain'])){
        //      $services=Config::get('services');       
        //     $mail['from'] =array('address' => $settings['mail_from'], 'name' => $settings['mail_from_name']);
        //     $services['mailgun'] =array('domain' => $settings['mailgun_domain'], 'secret' => $settings['mailgun_secret']);
        //     Config::set('mail', $mail);
        //     Config::set('services', $services);
        // }
       
    }
}