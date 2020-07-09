<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Admin\CronsController;

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
        $schedule->command('telescope:prune')->daily();
        
        $schedule->call(function () {
            $CronsController=new CronsController;
            $CronsController->delete3MonthHoldIncome();
        })->dailyAt('03:00');

        $schedule->call(function () {
            $CronsController=new CronsController;
            $CronsController->generateMonthlyPayout();
        })->monthlyOn(1, '01:00');

        // $schedule->call(function () {
        //     $CronsController=new CronsController;
        //     $CronsController->PVImport();
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
