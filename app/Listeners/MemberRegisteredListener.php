<?php

namespace App\Listeners;

use App\Events\MemberRegisteredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Admin\Email;
use App\Mail\CustomHtmlMail;
use Mail;
use App\Notifications\MemberRegisteredNotification;

class MemberRegisteredListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MemberRegisteredEvent  $event
     * @return void
     */
    public function handle(MemberRegisteredEvent $event)
    {
        $user=$event->user;
        $user->notify(new MemberRegisteredNotification());
    }
}
