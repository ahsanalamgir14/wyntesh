<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $table = 'incomes';
    public $timestamps = true;

    public function income_parameters()
    {
        return $this->hasMany('App\Models\Admin\IncomeParameter');
    }

}
