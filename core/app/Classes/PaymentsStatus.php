<?php
/**
 * Written by Farbod Nasiri
 */

namespace App\Classes;


/**
 * Class AppointmentStatus
 * @package App\Classes
 */
class PaymentsStatus
{
    const SUCCESS = 2;
    const FAILED  = 1;

    public static function getStatuses()
    {
        return [
            self::SUCCESS,
            self::FAILED,
        ];
    }
}
