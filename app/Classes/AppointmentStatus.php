<?php
/**
 * Written by Farbod Nasiri
 */

namespace App\Classes;


/**
 * Class AppointmentStatus
 * @package App\Classes
 */
class AppointmentStatus
{
    const INACTIVE = 2;
    const ACTIVE   = 1;

    public static function getStatuses()
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
        ];
    }
}
