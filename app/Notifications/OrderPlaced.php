<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\User\Order;
use App\Models\Admin\Sms;
use App\Models\Admin\Setting;
use App\Channels\SmsChannel;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;
use App\Models\Admin\NotificationSetting;
use App\Classes\SmsServiceHandler;

class OrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $order;
    public function __construct(Order $order)
    {
        $this->order=$order;
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
        $NotificationSetting=NotificationSetting::where('alias','order_placed')->first();
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
                    ->greeting('Hi, '.$this->order->user->name)
                    ->subject('New Order Placed')
                    ->line('You have placed order of amount - '.$this->order->net_amount);
    }

    public function toSms($notifiable)
    {
        $Sms=Sms::where('title','Order Placed SMS')->first();        
        if($Sms){
            $SmsServiceHandler=new SmsServiceHandler;
            $sms_content=$Sms->description;
            $site_url=Setting::getValue('website');

            $sms_content=str_replace("{name}",$this->order->user->name,$sms_content);
            $sms_content=str_replace("{username}",$this->order->user->username,$sms_content);
            $sms_content=str_replace("{mobile_number}",$this->order->user->contact,$sms_content);
            $sms_content=str_replace("{order_no}",$this->order->order_no,$sms_content);
            $sms_content=str_replace("{net_amount}",$this->order->net_amount,$sms_content);
            $sms_content=str_replace("{site_name}",$site_url,$sms_content);
            $contact_no=(double)$this->order->user->contact;
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
