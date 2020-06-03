<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class IncomeParameter extends Model
{
    protected $table = 'income_parameters';
    public $timestamps = true;

   
    public function income()
    {
        return $this->belongsTo('App\Models\Admin\Income');
    }

}
