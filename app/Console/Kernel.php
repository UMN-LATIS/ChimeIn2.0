<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel {
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
    protected function schedule(Schedule $schedule) {
        Log::warning("Schedule: Running scheduled tasks");
        $schedule->call(function () {
            Log::warning("Schedule: Running prune");
            DB::delete('DELETE FROM users WHERE NOT EXISTS (SELECT * FROM chime_user WHERE chime_user.user_id = users.id) AND  NOT EXISTS (SELECT * FROM responses WHERE responses.user_id=users.id) and DATEDIFF( CURDATE(), updated_at ) > 15');
        })->daily();


        $schedule->call(function () {
            Log::warning("Schedule: Running LTI sync");
            \App\Library\LTI13Processor::periodicTask();
        })->everyFiveMinutes();

        // see: https://laravel.com/docs/10.x/upgrade#redis-cache-tags
        $schedule->command('cache:prune-stale-tags')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
