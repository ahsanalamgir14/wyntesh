<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model {


	protected $table = 'categories';
	public $timestamps = true;
    use SoftDeletes;

    public function products()
    {
        return $this->hasMany('App\Models\Admin\Product');
    }

    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id');
    }

}