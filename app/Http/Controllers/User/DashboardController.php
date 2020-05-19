<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use JWTAuth;

class DashboardController extends Controller
{
    
    public function stats(){
        $courses=1;
        $users=User::count();
        $quizzes=1;
        $topics=1;

        $response = array('status' => true,'message'=>'Stats recieved','stats'=>array('courses'=>$courses,'users'=>$users,'quizzes'=>$quizzes,'topics'=>$topics));             
        return response()->json($response, 200);

    }
}
