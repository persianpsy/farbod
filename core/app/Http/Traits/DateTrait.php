<?php


namespace App\Http\Traits;


use Carbon\Carbon;

trait DateTrait
{
    public function calculateAge($date)
    {
        return Carbon::parse($date)->age;
    }
}
