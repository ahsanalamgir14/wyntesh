<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\User\Order;
use App\Channels\SmsChannel;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;
use App\Models\Admin\NotificationSetting;

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
                    ->line('You have placed order of amount - '.$this->order->final_amount);
    }

    public function toSms($notifiable)
    {
        $message='Hi, '.$this->order->user->name.chr(10);
        $message.='Order has been placed, your order number is #'.$this->order->order_no;
        return LaravelMsg91::message($this->order->user->contact,$message);
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
