<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class lastDayReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->data['location'] == 'Iran'){
            return $this
                ->from('therapypanel@persianpsychology.com','یادآوری جلسه مشاوره – Persian Psychology')
                ->view('emails.en.lastDayReminder');
        }else{
            return $this
                ->from('therapypanel@persianpsychology.com','Persian Psychology - Session reminder')
                ->view('emails.en.lastDayReminder');
        }
    }
}
