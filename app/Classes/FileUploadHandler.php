<?php

namespace App\Classes;
use Illuminate\Http\Request;
use Storage;
use Image;

class FileUploadHandler 
{    

	public function __construct()
    {
      
    }

    public function uploadFile(Request $request,$file_name,$directory)
    {
        $file = $request->file($file_name);
        $str=rand(); 
        $randomID = md5($str);
        $filename=$randomID.".".$file->getClientOriginalExtension();          
        $project_directory=env('DO_STORE_PATH');

        $store=Storage::disk('spaces')->put($project_directory.'/'.$directory.'/'.$filename, file_get_contents($file->getRealPath()), 'public');
        $url=Storage::disk('spaces')->url($project_directory.'/'.$directory.'/'.$filename);        
        $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

        return $cdn_url;
    }

    public function uploadThumbFile(Request $request,$file_name,$directory,$size=400)
    {
        $file = $request->file($file_name);
        $str=rand(); 
        $randomID = md5($str);
        $filename=$randomID."-thumb.".$file->getClientOriginalExtension();          
        $project_directory=env('DO_STORE_PATH');

        $thumb_img = Image::make($file);

        $thumb_img->resize($size, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $thumb_resized = $thumb_img->stream()->detach();

        $store=Storage::disk('spaces')->put($project_directory.'/'.$directory.'/'.$filename, $thumb_resized, 'public');
        $url=Storage::disk('spaces')->url($project_directory.'/'.$directory.'/'.$filename);        
        $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

        return $cdn_url;
    }

    public function deleteFile($url,$directory)
    {
        $project_directory=env('DO_STORE_PATH');
        $filePath=($project_directory.'/'.$directory.'/'.basename($url));            
        Storage::disk('spaces')->delete($filePath);
        return true;
    }

}
