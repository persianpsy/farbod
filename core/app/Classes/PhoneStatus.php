<?php
/**
 * Written by Farbod Nasiri
 */

namespace App\Classes;


/**
 * Class PhoneStatus
 * @package App\Classes
 */
class PhoneStatus
{
    const VERIFIED = 1;
    const UNVERIFIED = 0;

    public static function getStatuses()
    {
        return [
            self::UNVERIFIED,
            self::VERIFIED,
        ];
    }
}
