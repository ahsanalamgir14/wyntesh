<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ActivationLog extends Model
{
    protected $table = 'activation_logs';
    public $timestamps = true;


    public function user()
    {
        return $this->belongsTo('App\Models\User\User','user_id');
    }

    public function by()
    {
        return $this->belongsTo('App\Models\User\User','by_user');
    }


}
