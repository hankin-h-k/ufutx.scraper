<?php

namespace App\Console;

use CronService; 
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

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

    protected $mobile='18682191714';

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        
        //定时归还任务
        $schedule->call(function () {
            Log::debug('Library cron returnTip');
            try{
                CronService::returnTip();
            }catch(\Exception $e){
                $message = 'Library Cron returnTip :'.$e->getMessage();
                Log::debug($message);
                CronService::sentMessage($this->mobile, $message);
                Log::debug($e->getTraceAsString());
            }
        })->saturdays()->dailyAt('10:30');

        //检查formId
        $schedule->call(function () {
            Log::debug('check formId');
            try{
                CronService::checkFormId();
            }catch(\Exception $e){
                $message = 'check formId :'.$e->getMessage();
                Log::debug($message);
                CronService::sentMessage($this->mobile, $message);
                Log::debug($e->getTraceAsString());
            }
        })->daily();
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
