<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Admin\JobModel;
use App\Models\Admin\Email;
use App\Models\User\User;
use App\Mail\CustomHtmlMail;
use Mail;
use Illuminate\Support\Facades\Log;

class SendMassEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $Email;
    protected $JobModel;

    public function __construct(Email $Email,JobModel $JobModel)
    {
        $this->Email = $Email;
        $this->JobModel = $JobModel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $user_list=explode(',', $this->JobModel->models);
        $Users=User::whereIn('id',$user_list)->get();

        $failed_models=[];
        $is_failed=0;

        foreach ($Users as $User) {
            try{
                $mail=Mail::to($User->email)->send(new CustomHtmlMail($this->Email->description));    
                
            }catch (\Exception $e) {
                
                $failed_models[]=intval($User->id);
                $is_failed=1;
            }
            
        }

        $this->JobModel->failed_models=implode(',', $failed_models);
        $this->JobModel->is_failed=$is_failed;
        $this->JobModel->is_finished=1;
        $this->JobModel->save();
         

    }
}
