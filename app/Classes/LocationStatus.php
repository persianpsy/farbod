<?php
/**
 * Written by Farbod Nasiri
 */

namespace App\Classes;


/**
 * Class locationtStatus
 * @package App\Classes
 */
class LocationStatus
{
    const IR = 1;
    const OUT = 0;

    public static function getStatuses()
    {
        return [
            self::IR,
            self::OUT,
        ];
    }
}
