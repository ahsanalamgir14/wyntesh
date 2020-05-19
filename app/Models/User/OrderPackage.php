<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPackage extends Model
{
    protected $table = 'order_packages';
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\User\Order');
    }

    public function package()
    {
        return $this->belongsTo('App\Models\Admin\Package');
    }
}
