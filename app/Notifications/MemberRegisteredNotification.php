<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Admin\Setting;
use App\Channels\SmsChannel;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;
use App\Models\Admin\NotificationSetting;
use App\Models\Admin\Email;
use App\Models\Admin\Sms;
use App\Mail\CustomHtmlMail;
use App\Classes\SmsServiceHandler;

class MemberRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        //return ['mail',SmsChannel::class];
        $channels=[];
        $NotificationSetting=NotificationSetting::where('alias','new_registration')->first();
        if($NotificationSetting){
            if($NotificationSetting->is_email){
                $channels[]='mail';
            }
            if($NotificationSetting->is_sms){
                $channels[]=SmsChannel::class;
            }
            return $channels;
        }else{
            return $channels;    
        }
        
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {   
        $Email=Email::where('title','Welcome Email')->first();        
        if($Email){
            $email_content=$Email->description;
            $email_content=str_replace("{name}",$notifiable->name,$email_content);
            $email_content=str_replace("{username}",$notifiable->username,$email_content);            
            return (new CustomHtmlMail($email_content,'Registration Successful'))->subject('Registration Successful')->to($notifiable->email);
        }
    }

    public function toSms($notifiable)
    {
        $Sms=Sms::where('title','Welcome SMS')->first();        
        if($Sms){
            $SmsServiceHandler=new SmsServiceHandler;
            $sms_content=$Sms->description;
            $site_name=Setting::getValue('site_name');
            $sms_content=str_replace("{name}",$notifiable->name,$sms_content);
            $sms_content=str_replace("{username}",$notifiable->username,$sms_content);
            $sms_content=str_replace("{site_name}",$site_name,$sms_content);
            $contact_no=(double)$notifiable->contact;
            return $SmsServiceHandler->sendSMS($contact_no,$sms_content,0);
        }

    }
}
