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

    public function rank()
    {
        return $this->belongsTo('App\Models\Admin\Rank','rank_id');
    }

    public function kyc()
    {
        return $this->hasOne('App\Models\User\Kyc');
    }

    public function leg()
    {
        return $this->hasMany('App\Models\Admin\MemberLegPv');
    }

    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id')->with('user:id,username,name,is_active');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user:id,username,name,is_active')->with('kyc:id,member_id,verification_status,is_verified');
    }

    public function sponsor()
    {
        return $this->belongsTo(self::class,'sponsor_id')->with('user:id,username,name,is_active');
    }
}
