<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    
	protected $table = 'tickets';

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function assigned()
    {
        return $this->belongsTo('App\Models\User\User','assigned_to','id');
    }

    public function conversations()
    {
        return $this->hasMany('App\Models\User\TicketConversation');
    }


}
