<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Mail;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;

use App\Models\User\User;
use App\Models\Admin\JobModel;
use App\Mail\CustomHtmlMail;
use App\Models\Admin\Email;
use App\Jobs\SendMassEmailsJob;
use App\Jobs\SendMassSMSJob;
use Illuminate\Support\Facades\Log;


class MarketingController extends Controller
{
    public function sendMassEmail(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email_id'=>'required|integer',
            'users'=>'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $JobModel= new JobModel;
        $JobModel->type="emails";
        $JobModel->models=implode(',', $request->users);
        $JobModel->is_queued=1;
        $JobModel->save();

        
        $Email=Email::find($request->email_id);

        SendMassEmailsJob::dispatch($Email,$JobModel);
        $response = array('status' => true,'message'=>"Emails are queued for sending.");
        return response()->json($response, 200);
    }

    public function sendMassSMS(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'message'=>'required',
            'users'=>'required',
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $JobModel= new JobModel;
        $JobModel->type="sms";
        $JobModel->models=implode(',', $request->users);
        $JobModel->is_queued=1;
        $JobModel->save();


        SendMassSMSJob::dispatch($request->message,$JobModel);

        $response = array('status' => true,'message'=>"SMS are queued for sending.");
        return response()->json($response, 200);
    }
}
