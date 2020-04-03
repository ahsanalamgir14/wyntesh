<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Admin\News;
use App\Models\User\User;
use Storage;
use JWTAuth;

class NewsesController extends Controller
{    

   

    public function saveNews(Request $request){
        $validate=News::validator($request);
        
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $News=News::latest('id')->where('tag_id',$request->tag_id)->first();

        if(!$News){
            $News=new News;    
        }
        
        $News->title=$request->title;
        $News->description=$request->description;
        $News->tag_id=$request->tag_id;
        $News->save();

        $response = array('status' => true,'message'=>'News saved successfully.','data'=>$News);
        return response()->json($response, 200);
    }

    public function getNews($tag_id){
        $News=News::latest('id')->where('tag_id',$tag_id)->first();

        $response = array('status' => true,'message'=>'News retrieved successfully','data'=>$News);
        return response()->json($response, 200);
    }

    public function getMyNews(){
        $user_id=JWTAuth::user()->id;
        $User=User::find($user_id);
        $News=News::latest('id')->where('tag_id',$User->tag_id)->first();

        $response = array('status' => true,'message'=>'News retrieved successfully','data'=>$News);
        return response()->json($response, 200);
    }

}
