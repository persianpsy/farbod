<?php
/**
 * Written by Farbod Nasiri
 */

namespace App\Classes;


/**
 * Class ReservationStatus
 * @package App\Classes
 */
class ReservationStatus
{
    const CANCEL = 7;
    const FINISHED = 6;
    const JOINEDROOM = 5;
    const CREATEDROOM = 4;
    const FAILED = 3;
    const PAID = 2;
    const CREATED =1;

    public static function getStatuses()
    {
        return [
            self::CREATED,
            self::PAID,
            self::FAILED,
            self::CREATEDROOM,
            self::JOINEDROOM,
            self::FINISHED,
            self::CANCEL,

        ];
    }
}
