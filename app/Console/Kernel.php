<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Admin\CronsController;
use App\Http\Controllers\User\PayoutsController;
use App\Http\Controllers\User\MembersController;
use App\Models\Admin\CompanySetting;
use App\Http\Controllers\Admin\MigrationController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];
    public $is_automatic_payout=0;

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {   
        //$schedule->command('telescope:prune')->daily();
        
        $schedule->call(function () {
            $CronsController=new CronsController;
            $CronsController->backupDatabase();
        })->dailyAt('23:58');

        // $schedule->call(function () {
        //     $CronsController=new CronsController;
        //     $CronsController->generateMonthlyPayout();
        // })->dailyAt('00:01');

        $this->is_automatic_payout=CompanySetting::getValue('is_automatic_payout');

        $schedule->call(function () {
            $CronsController=new CronsController;
            $CronsController->generateWeeklyPayout();
        })->weeklyOn(6, '00:05')->when(function () {
            if($this->is_automatic_payout == 1) {
                return true;
            } else false;
        });

        $schedule->call(function () {
            $CronsController=new CronsController;
            $CronsController->WallOfWyntashReport();
        })->monthlyOn(1, '00:30');

        // $schedule->call(function () {
        //     $CronsController=new CronsController;
        //     $CronsController->releaseHoldPayout();
        // })->monthlyOn(1, '00:22');

        // $schedule->call(function () {
        //     $MigrationController=new MigrationController;
        //     $MigrationController->doMigration();
        // });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
