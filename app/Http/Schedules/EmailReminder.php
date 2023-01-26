<?php

namespace App\Http\Schedules;

use App\Http\Traits\AppointmentTrait;
use App\Models\Appointment;
use App\Models\Holiday;
use App\Models\InsuranceSpecialty;
use App\Models\Log;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffSpecialty;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use http\Env\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Morilog\Jalali\Jalalian;
use Symfony\Component\DomCrawler\Crawler;

class EmailReminder
{
    public function __invoke()
    {
        $next_five_reservations = Reservation::where('appointment.datetime', '>=', Carbon::now()->addMinutes(5)->toDateTimeString())->get();
        foreach ($next_five_reservations as $reservation){
            Mail::to($reservation->user->email)->send(New \App\Mail\EmailReminder($reservation));
        }
    }
}
