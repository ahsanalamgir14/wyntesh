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
use App\Mail\CustomHtmlMail;

class MemberRegisteredNotification extends Notification
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
            return (new CustomHtmlMail($email_content))->subject('Registration Successful')->to($notifiable->email);
        }
    }

    public function toSms($notifiable)
    {
        $company_name=Setting::getValue('company_name');
        $website=Setting::getValue('website');
        $message='Hi, '.$notifiable->name.chr(10);
        $message.='Thanks for joining '.$company_name.chr(10);
        $message.='Please log in to '.$website.chr(10);
        $message.='Username - '.$notifiable->username.chr(10);
        $message.='Password - ******'.chr(10);
        $message.='Wish you a prosperous future.'.chr(10);
        return LaravelMsg91::message($notifiable->contact,$message);
    }
}
