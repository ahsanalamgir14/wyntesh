<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SmsChannel;
use App\Models\Admin\Email;
use App\Mail\CustomHtmlMail;
use App\Models\Admin\NotificationSetting;
use App\Models\Admin\MemberPayout;
use App\Models\Admin\MemberPayoutIncome;
use App\Models\Admin\Setting;
use App\Models\Admin\Sms;
use App\Classes\SmsServiceHandler;

class PayoutNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $MemberPayout;

    public function __construct(MemberPayout $MemberPayout)
    {
        $this->MemberPayout=$MemberPayout;
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
        $NotificationSetting=NotificationSetting::where('alias','payout_generation')->first();
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
        $Email=Email::where('title','Payout Email')->first();
        if($Email){
            
            $total_income=MemberPayoutIncome::whereNotIn('income_id',[1,2])->where('member_id',$this->MemberPayout->member_id)->where('member_payout_id',$this->MemberPayout->id)->sum('net_payable_amount');

            $email_content=$Email->description;
            $email_content=str_replace("{name}",$this->MemberPayout->member->user->name,$email_content);
            $email_content=str_replace("{payout_amount}",$total_income,$email_content);
            $email_content=str_replace("{username}",$this->MemberPayout->member->user->username,$email_content);            
            $email_content=str_replace("{mobile_number}",$this->MemberPayout->member->user->contact,$email_content);            
            $subject='You just got paid !';
            return (new CustomHtmlMail($email_content,$subject))->to($this->MemberPayout->member->user->email);
        }
    }

    public function toSms($notifiable)
    {
        
        $Sms=Sms::where('title','Payout SMS')->first();        
        if($Sms){
            $total_income=MemberPayoutIncome::whereNotIn('income_id',[1,2])->where('member_id',$this->MemberPayout->member_id)->where('member_payout_id',$this->MemberPayout->id)->sum('net_payable_amount');

            $SmsServiceHandler=new SmsServiceHandler;
            $sms_content=$Sms->description;
            $site_name=Setting::getValue('site_name');

            $sms_content=str_replace("{name}",$this->MemberPayout->member->user->name,$sms_content);
            $sms_content=str_replace("{username}",$this->MemberPayout->member->user->username,$sms_content);
            $sms_content=str_replace("{payout_amount}",$total_income,$sms_content);
            $sms_content=str_replace("{site_name}",$site_name,$sms_content);
            $contact_no=(double)$this->MemberPayout->member->user->contact;
            return $SmsServiceHandler->sendSMS($contact_no,$sms_content,0);
        }

    }
}
