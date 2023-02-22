<?php

namespace App\Console;

use App\Http\Schedules\EmailReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use App\Http\Traits\SmsTrait;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
      use SmsTrait;
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
        $schedule->call(function () {

       Log::info('time.', ['res' => 'time']);
       $id=[];
        $data =  Reservation::with('appointment','user','staff','staff.user')->whereIn('status',[2])->whereHas('appointment',function ($q) use($id) {
              $q->where('date' ,'=',\verta()->formatDate())->whereBetween ('time',[\verta()->subMinutes(30)->format('H:i'),\verta()->addMinutes(30)->format('H:i')]);

        })->get();

        foreach ($data as $title) {

          $res_client =  $this->SendAuthCode($title->user->cellphone,'remiders',$title->appointment->date,$title->appointment->time);
            $res_dr =  $this->SendAuthCode($title->staff->user->cellphone,'remiders',$title->appointment->date,$title->appointment->time);

                 Log::info('res reminder kavenegar.', ['res_dr' => $res_dr]);
                 Log::info('res reminder kavenegar.', ['res_client' =>$res_client]);

        }



        })->hourly();

           $schedule->call(function () {


        $id=[];
        $data2 =  Reservation::with('appointment','user','staff','staff.user')->whereIn('status',[2])->whereHas('appointment',function ($q) use($id) {
              $q->where('date' ,'=',\verta()->formatDate());
            })->get();

        foreach ($data2 as $title) {

            $res_client =  $this->SendAuthCode($title->user->cellphone,'remiders',$title->appointment->date,$title->appointment->time);
            $res_dr =  $this->SendAuthCode($title->staff->user->cellphone,'remiders',$title->appointment->date,$title->appointment->time);

                 Log::info('res reminder kavenegar.', ['res_dr' => $res_dr]);
                 Log::info('res reminder kavenegar.', ['res_client' =>$res_client]);
        }



        })->daily();

        $schedule->command('queue:work')->everyMinute();
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
