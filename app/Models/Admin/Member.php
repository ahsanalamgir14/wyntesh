<?php

namespace App\Models\Admin;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use DB;
class Member extends Model
{
    public $timestamps = true;
    protected $appends = ['group_pv'];
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
        return $this->hasMany('App\Models\Admin\MembersLegPv');
    }

    public function getGroupPvAttribute()
    {
        $group_pv=0;
        $legs=$this->leg_pv;
        foreach ($legs as $leg) {
            $group_pv+=$leg->pv;
        }
        return $group_pv;
    }


    public function leg_pv()
    {
        return $this->hasMany('App\Models\Admin\MemberMonthlyLegPv');
    }

    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id')->with('user:id,username,name,is_active');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user:id,username,name,is_active')->with('kyc:id,member_id,verification_status,is_verified')->with('leg')->with('rank')
            ->withCount(['leg as group_pv' => function($query){
               $query->select(DB::raw('sum(total_pv)'));
            }]);
    }

    public function sponsored()
    {
        return $this->hasMany(self::class, 'sponsor_id')->with('user:id,username,name,is_active')->with('kyc:id,member_id,verification_status,is_verified');
    }

    public function sponsor()
    {
        return $this->belongsTo(self::class,'sponsor_id')->with('user:id,username,name,is_active');
    }
}
