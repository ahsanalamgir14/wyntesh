<?php

namespace App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

class StockLogs extends Model
{
    public $timestamps = true;

    public function files()
    {
        return $this->hasMany('App\Models\Admin\StockLogsFiles','stock_log_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Admin\Product');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\User\Order');
    }

    public function variant()
    {
        return $this->belongsTo('App\Models\Admin\ProductVariant');
    }
}
