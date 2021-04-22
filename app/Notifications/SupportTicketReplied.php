<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\User\Ticket;
use App\Models\Admin\Sms;
use App\Models\Admin\Setting;
use App\Channels\SmsChannel;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;
use App\Models\Admin\NotificationSetting;
use App\Classes\SmsServiceHandler;

class SupportTicketReplied extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket=$ticket;
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
        $NotificationSetting=NotificationSetting::where('alias','support_ticket_replied')->first();
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
        return (new MailMessage)
                    ->greeting('Hi, '.$this->ticket->user->name)
                    ->subject('Ticket '.$this->ticket->id.', Responded')
                    ->line('Your query with ticket no - '.$this->ticket->id.' has been resolved or responded, please check your account for more details.');
    }

    public function toSms($notifiable)
    {
        $Sms=Sms::where('title','Support Ticket Reply')->first();        
        if($Sms){
            $SmsServiceHandler=new SmsServiceHandler;
            $sms_content=$Sms->description;
            $site_url=Setting::getValue('website');

            $sms_content=str_replace("{name}",$this->ticket->user->name,$sms_content);
            $sms_content=str_replace("{username}",$this->ticket->user->username,$sms_content);
            $sms_content=str_replace("{ticket_id}",$this->ticket->id,$sms_content);
            $contact_no=(double)$this->ticket->user->contact;
            return $SmsServiceHandler->sendSMS($contact_no,$sms_content,0);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
