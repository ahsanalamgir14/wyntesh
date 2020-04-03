<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
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
