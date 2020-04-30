<?php

namespace App\Models\Admin;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function kyc()
    {
        return $this->hasOne('App\Models\User\Kyc');
    }

    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user')->with('kyc');
    }

    public function sponsor()
    {
        return $this->belongsTo(self::class,'sponsor_id');
    }
}
