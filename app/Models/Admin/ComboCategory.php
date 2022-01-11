<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComboCategory extends Model
{
    protected $table = 'combo_categories';
    public $timestamps = true;
    use SoftDeletes;

    public function category()
    {
        return $this->belongsTo('App\Models\Admin\Category','category_id');
    }

    public function combo()
    {
        return $this->belongsTo('App\Models\Admin\Combo');
    }

}
