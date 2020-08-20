<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use Illuminate\Support\Facades\Log;

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
        Log::warning("Schedule: Running scheduled tasks");
        $schedule->call(function () {
            Log::warning("Schedule: Running prune");
            DB::delete('DELETE FROM users WHERE NOT EXISTS (SELECT * FROM chime_user WHERE chime_user.user_id = users.id) AND  NOT EXISTS (SELECT * FROM responses WHERE responses.user_id=users.id) and DATEDIFF( CURDATE(), updated_at ) > 15');
        })->daily();

        
        $schedule->call(function() {
            Log::warning("Schedule: Running LTI sync");
            $folders = \App\Folder::join("questions", "folders.id", "=", "questions.folder_id")->join("sessions", "questions.id","=","sessions.question_id")
            ->join("responses", "sessions.id","=","responses.session_id")
            ->select("folders.*")
            ->whereNotNull("folders.resource_link_pk")
            ->whereBetween('responses.updated_at', [now()->subMinutes(10), now()])->get()->unique();

            foreach($folders as $folder) {
                \App\Library\LTIProcessor::syncFolder($folder);
            }

            $chimes = \App\Chime::join("folders", "chimes.id", "=", "folders.chime_id")->join("questions", "folders.id", "=", "questions.folder_id")->join("sessions", "questions.id","=","sessions.question_id")
            ->join("responses", "sessions.id","=","responses.session_id")
            ->select("chimes.*")
            ->whereNotNull("chimes.resource_link_pk")
            ->where("chimes.single_chime_for_lti", 1)
            ->whereBetween('responses.updated_at', [now()->subMinutes(10), now()])->get()->unique();

            foreach($chimes as $chime) {
                \App\Library\LTIProcessor::syncChime($chime);
            }

        })->everyFiveMinutes();
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
