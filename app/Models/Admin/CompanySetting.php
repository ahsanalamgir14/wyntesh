<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
	protected $table = 'company_settings';

    public function scopeGetValue($query, $key = '')
    {
        $setting = $query->where('key',$key)->first();
        if ($setting) {
            return $setting->value;
        } else {
            return false;
        }
    }
}
