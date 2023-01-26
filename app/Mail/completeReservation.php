<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class completeReservation extends Mailable
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
        // if ($this->data['location'] == 'Iran'){
        //     return $this
        //         ->from('therapypanel@persianpsychology.com','ثبت رزرواسیون – Persian Psychology')
        //         ->view('emails.en.CompleteReservation');
        // }else{
        //     return $this
        //         ->from('therapypanel@persianpsychology.com','Persian Psychology – booking session')
        //         ->view('emails.en.completeReservation');
        // }
         $address = 'it@persianpsychology.com';
        $subject = 'This is a demo!';
        $name = 'PersianPsychology';

        return $this->markdown('emails.en.completeReservation')
                    ->from($address, $name)
                    ->cc($address, $name)
                    ->bcc($address, $name)
                    ->replyTo($address, $name)
                    ->subject($subject)
                    ->with('data' ,  $this->data);
    }
}
