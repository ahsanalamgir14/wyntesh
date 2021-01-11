<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProduct extends Model
{
    protected $table = 'order_products';
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\User\Order');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Admin\Product');
    }

    public function variant()
    {
        return $this->belongsTo('App\Models\Admin\ProductVariant');
    }
}
