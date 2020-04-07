<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class Testimonial extends Model {


	protected $table = 'testimonials';
	public $timestamps = true;

	public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|max:256',
            'description' => 'required|max:2048'
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|max:256',
            'description' => 'required|max:2048'
        ]);
    }

}