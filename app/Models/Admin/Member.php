<?php

namespace App\Models\Admin;

use App\Models\User\User;
use App\Http\Controllers\User\MembersController;
use Illuminate\Database\Eloquent\Model;
use DB;
class Member extends Model
{
    public $timestamps = true;
    protected $appends = ['group_pv','team_size'];
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function rank()
    {
        return $this->belongsTo('App\Models\Admin\Rank','rank_id');
    }

    public function rank_logs()
    {
        return $this->hasMany('App\Models\Admin\RankLog', 'member_id');
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
        $legs=$this->leg;
        foreach ($legs as $leg) {
            $group_pv+=$leg->pv;
        }
        return $group_pv;
    }

    public function getTeamSizeAttribute()
    {
        $MembersController=new MembersController;
        $childs=$MembersController->getChildsOfParent($this->id);
        if($childs){
            return count($childs);
        }else{
            return 0;
        }
    }

    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id')->with('user:id,username,name,is_active');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user:id,username,name,is_active')->with('kyc:id,member_id,verification_status,is_verified')->with('leg')->with('rank')
            ->withCount(['leg as group_pv' => function($query){
               $query->select(DB::raw('sum(pv)'));
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
