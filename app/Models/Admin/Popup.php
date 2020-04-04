<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Validator;

class Popup extends Model {


	protected $table = 'popups';
	public $timestamps = true;

	//use SoftDeletes;
	//protected $dates = ['deleted_at'];

	public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|max:2048'
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|max:2048'
        ]);
    }

}