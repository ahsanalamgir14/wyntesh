<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\CompanySetting;
class SettingsController extends Controller
{
    
    public function getMemberSettings()
    {
        $settings= CompanySetting::
                    pluck('value', 'key')
                    ->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }
}
