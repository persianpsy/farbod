<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\SmsTrait;
use Illuminate\Support\Facades\Log;
use Kavenegar;

class smsReminder implements ShouldQueue
{
    use Dispatchable,SmsTrait, InteractsWithQueue, Queueable, SerializesModels;
    protected $phone;
    protected $token;
    protected $pattern;
    protected $token2;
    protected $token3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phone, $pattern, $token, $token2 = '', $token3 = '')
    {
        $this->phone = $phone;
        $this->token = $token;
        $this->pattern = $pattern;
        $this->token2 = $token2;
        $this->token3 = $token3;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $res = $this->SendAuthCode($this->phone,$this->pattern,$this->token);

        } catch (\Exception $e) {
//            Log::alert([$this->phone, $token, $e->getMessage(), $this->pattern]);
        }


    }
}
