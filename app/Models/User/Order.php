<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {


	protected $table = 'orders';
	public $timestamps = true;
	use SoftDeletes;
  
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function products()
    {
        return $this->hasMany('App\Models\User\OrderProduct')->with('product');
    }

    public function packages()
    {
        return $this->hasMany('App\Models\User\OrderPackage')->with('package');
    }

    public function logs()
    {
        return $this->hasMany('App\Models\User\DeliveryLog');
    }

    public function payment_mode()
    {
        return $this->belongsTo('App\Models\Superadmin\PaymentMode','payment_mode');
    }


}