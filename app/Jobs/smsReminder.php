<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Kavenegar;

class smsReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cellphone;
    public $message;

    /**
     * Create a new job instance.
     *
     * @param $cellphone
     * @param $message
     */
    public function __construct($cellphone,$message)
    {
        $this->cellphone = $cellphone;
        $this->message = $message;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

     
        $mail =  Mail::to($this->cellphone)->send(new \App\Mail\otpMail ($this->message));  
             
    }
}
