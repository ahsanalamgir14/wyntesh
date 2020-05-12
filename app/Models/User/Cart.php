<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model {


	protected $table = 'cart';
	public $timestamps = true;

    public function products()
    {
        return $this->hasMany('App\Models\Admin\Product');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }


}