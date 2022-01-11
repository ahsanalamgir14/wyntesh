<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Combo extends Model
{
    public $timestamps = true;
    use SoftDeletes;

    public function categories()
    {
        return $this->hasMany('App\Models\Admin\ComboCategory');
    }
}
