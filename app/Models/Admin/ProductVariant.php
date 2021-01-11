<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    public function color()
    {
        return $this->belongsTo('App\Models\Admin\ColorVariant');
    }
    public function size()
    {
        return $this->belongsTo('App\Models\Admin\SizeVariant');
    }
    public function product()
    {
        return $this->belongsTo('App\Models\Admin\Product');
    }

}
