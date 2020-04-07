<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class BankPartner extends Model {


	protected $table = 'bank_partners';
	public $timestamps = true;

	public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|max:256',
            'branch_name' => 'required|max:64',
            'account_type' => 'required|max:64',
            'account_holder_name' => 'required|max:256'
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|max:256',
            'branch_name' => 'required|max:64',
            'account_type' => 'required|max:64',
            'account_holder_name' => 'required|max:256'
        ]);
    }

}