<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class Gallery extends Model {


	protected $table = 'gallery';
	public $timestamps = true;

	public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,png,gif'
        ]);
    }

}