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
        return $this->hasMany('App\Models\User\OrderProduct')->with('product:id,name,product_number,pv,qty,qty_unit,retail_amount,retail_base,retail_gst,shipping_fee,gst_rate,discount_amount,discount_rate,cover_image_thumbnail,brand_name');
    }

    public function logs()
    {
        return $this->hasMany('App\Models\User\DeliveryLog');
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