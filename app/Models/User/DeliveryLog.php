<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryLog extends Model
{
    protected $table = 'delivery_logs';
    public $timestamps = true;

    public function order()
    {
        return $this->belongsTo('App\Models\User\Order');
    }
}
