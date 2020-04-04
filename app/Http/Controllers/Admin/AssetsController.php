<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Datatables;
use Validator;
use App\Setting;
use Illuminate\Support\Facades\Storage;

class AssetsController extends Controller
{
    public function imageUpload(Request $request)
    {
        if($request->hasFile('file')){
            $file = $request->file('file');
            $rand=rand(1,54455454);
            $rand=time().$rand;
            $filename=$rand.".".$file->getClientOriginalExtension();
            $setting=Setting::where('key','alias')->first();
            $client_alias=$setting->value;
           
            $store=Storage::disk('spaces')->put($client_alias.'/assets/'.$filename, file_get_contents($request->file('file')), 'public');
            $resource=Storage::disk('spaces')->url($client_alias.'/assets/'.$filename);

            $resource=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $resource);

            if($store){
               
                return ['success' => true,'url' => $resource];
            }
        }
    }
}
