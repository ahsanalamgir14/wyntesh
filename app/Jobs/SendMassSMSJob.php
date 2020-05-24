<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Admin\JobModel;
use App\Models\User\User;
use RobinCSamuel\LaravelMsg91\Facades\LaravelMsg91;

class SendMassSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $message;
    protected $JobModel;

    public function __construct($message,JobModel $JobModel)
    {
        $this->message = $message;
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
                $Msg = LaravelMsg91::message($User->contact,$this->message);
                
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
