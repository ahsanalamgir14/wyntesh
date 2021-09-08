<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class SoftwarePopup extends Model
{
    protected $table = 'software_popups';
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
