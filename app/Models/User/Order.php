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

    public function billing_address()
    {
        return $this->belongsTo('App\Models\User\Address','billing_address_id');
    }

    public function shipping_address()
    {
        return $this->belongsTo('App\Models\User\Address','shipping_address_id');
    }


}