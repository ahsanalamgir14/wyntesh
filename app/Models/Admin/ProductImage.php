<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProductImage extends Model {


	protected $table = 'product_images';
	public $timestamps = true;

    public function product()
    {
        return $this->belongsToOne('App\Models\Admin\Product');
    }

    public function variant()
    {
        return $this->belongsTo('App\Models\Admin\ProductVariant','variant_id','id');
    }

}