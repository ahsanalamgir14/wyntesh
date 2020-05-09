<?php

namespace App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    protected $table = 'products';
    public $timestamps = true;
    use SoftDeletes;


    public function categories()
    {
        return $this->belongsToMany('App\Models\Admin\Category','product_categories');
    }

    public function images()
    {
        return $this->hasMany('App\Models\Admin\ProductImage');
    }

}
