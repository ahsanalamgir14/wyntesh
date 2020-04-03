<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name','description','default_period','price','price','gst','gst_amount','finacl_price'
    ];

}
