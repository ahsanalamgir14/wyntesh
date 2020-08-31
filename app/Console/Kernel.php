<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Admin\CronsController;
use App\Http\Controllers\User\PayoutsController;

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
        })->dailyAt('23:55');

        $schedule->call(function () {
            $CronsController=new CronsController;
            $CronsController->generateMonthlyPayout();
        })->monthlyOn(31, '23:50');

        $schedule->call(function () {
            $CronsController=new CronsController;
            $CronsController->WallOfWyntashReport();
        })->monthlyOn(31, '23:55');

        // $schedule->call(function () {
        //     $CronsController=new CronsController;
        //     $CronsController->generateMonthlyPayout();
        // })->monthlyOn(1, '01:00');

        // $schedule->call(function () {
        //     $PayoutsController=new CronsController;
        //     $PayoutsController->generateMonthlyPayout();
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
