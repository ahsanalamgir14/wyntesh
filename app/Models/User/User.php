<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Http\Request;
use Validator;

class User extends  Authenticatable implements JWTSubject
{

    use HasRoles;
    use Notifiable;
    //protected $guard_name = 'web';

    protected $table = 'users';
    public $timestamps = true;

    protected $fillable = [
        'name','username','email','password','contact','gender','dob','verified_at','is_active','otp','otp_valid_till','google','parent'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','otp','otp_valid_till','google'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'username' => 'required|max:255|unique:users',
            'name' => 'required|max:255',
            'email' => 'required|unique:users',
            'password' => 'required|max:255',
            'dob'=>'date|date_format:Y-m-d',
            'gender'=>'max:1'
        ]);
    }

    public static function memberValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|regex:/^.+@.+$/i|unique:users',
            'contact' => 'required|max:10|regex:/^[0-9]{10}$/',
            'password' => 'required|max:255',
            'dob'=>'date|date_format:Y-m-d',
            'gender'=>'max:1'
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|max:255',
            'dob'=>'date|date_format:Y-m-d',
            'gender'=>'max:1'
        ]);
    }

    
    public function kyc()
    {
        return $this->hasOneThrough('App\Models\User\Kyc', 'App\Models\Admin\Member');
    }

    public function member()
    {
        return $this->hasOne('App\Models\Admin\Member');
    }

    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'name' => $this->name,
            'roles'=>$this->roles
        ];
    }

}

