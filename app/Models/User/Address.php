<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model {


	protected $table = 'addresses';
	public $timestamps = true;
	use SoftDeletes;

  
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }


}