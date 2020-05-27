<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Validator;

class NotificationSetting extends Model {

	protected $table = 'notification_settings';
	public $timestamps = true;

}