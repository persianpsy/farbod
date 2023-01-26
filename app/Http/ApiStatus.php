<?php
/**
 * Written By farbod
 */




interface ApiStatus
{
    const STATUS_OK              = 'ok';
    const STATUS_FAIL            = 'fail';
    const STATUS_EXPIRE          = 'expire';
    const STATUS_ERROR           = 'error';
    const STATUS_ACCESS_DENIED   = 'accessDenied';
    const STATUS_EXCEPTION_ERROR = 'exceptionError';
    const STATUS_UNAUTHENTICATED = 'unauthenticated';
    const STATUS_FORBIDDEN       = 'forbidden';
}
