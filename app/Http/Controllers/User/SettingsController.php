<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Setting;
class SettingsController extends Controller
{
    
    public function getMemberSettings()
    {
        $settings= Setting::where('is_public',0)
                    ->whereIn('key',['is_member_pin_transfer_enabled','tds_percentage'])
                    ->get()
                    ->pluck('value', 'key')
                    ->toArray();
        $response = array('status' => true,'message'=>'Settings retrived.','data'=>$settings);             
        return response()->json($response, 200);
    }
}
