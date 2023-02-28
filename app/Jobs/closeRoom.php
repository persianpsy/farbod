<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class closeRoom implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $phone;
    protected $token;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phone, $pattern)
    {
        $this->phone = $phone;
        $this->pattern = $pattern;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $url = 'https://www.skyroom.online/skyroom/api/apikey-19196080-5-717552ba3e8e72ccd0c272ee1838cbc6';
            $client = new \GuzzleHttp\Client();

            $response = $client->request('POST', $url, ['json' => [
                "action"=> $this->pattern,
                "params"=>[
                    "room_id"=> $this->phone,

                ]
            ]
            ]);


        } catch (\Exception $e) {
//            Log::alert([$this->phone, $token, $e->getMessage(), $this->pattern]);
        }
    }
}
