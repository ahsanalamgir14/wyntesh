<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TicketConversation extends Model
{
    
	protected $table = 'ticket_conversations';

    public function from_user()
    {
        return $this->belongsTo('App\Models\User\User','from','id');
    }

    public function to_user()
    {
        return $this->belongsTo('App\Models\User\User','to','id');
    }

    public function ticket()
    {
        return $this->belongsTo('App\Models\User\Ticket');
    }

    public function getCreatedAtAttribute($value)
   {
       return Carbon::createFromTimeStamp(strtotime($value))->diffForHumans();
   }
}
