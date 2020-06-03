<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class Download extends Model {


	protected $table = 'downloads';
	public $timestamps = true;

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