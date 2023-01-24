<?php
/**
 * Written by Farbod
 */

//use App\Classes\CacheKey;
use App\Fractal\Fractal;
use App\Fractal\Transformer;
use Carbon\Carbon;
use Hashids\Hashids;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Facades\Cache;
//use Modules\Core\Builders\UUID;

if(!function_exists('get_id')) {
    /**
     * Convert to id if it's hashed
     *
     * @param [type] $id
     * @return void
     */
    function get_id($id)
    {
//        return is_hashid($id) ? hashid($id) : $id;
    }
}



if(!function_exists('currency_format')) {

    /**
     * @param int $amount
     * @return string
     */
    function currency_format($amount)
    {
        $amount = (int) $amount;

        $length = strlen((string)$amount);
        $offset = $length % 3;

        if ($offset == 0) $offset = 3;
        $count = intval(($length - 1) / 3);

        for ($i = 0; $i<$count; $i++){
            $pos = $i * 3 + $offset + $i;
            $amount = substr_replace($amount, ',', $pos, 0);
        }

        return $amount;
    }
}

if(!function_exists('get_ip')) {

    /**
     * @return string
     */
    function get_ip()
    {
        foreach ([
                     'HTTP_CLIENT_IP',
                     'HTTP_X_FORWARDED_FOR',
                     'HTTP_X_FORWARDED',
                     'HTTP_X_CLUSTER_CLIENT_IP',
                     'HTTP_FORWARDED_FOR',
                     'HTTP_FORWARDED',
                     'REMOTE_ADDR'
                 ] as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }
}

if(!function_exists('ticks_to_timestamps')) {

    /**
     * @param $ticks
     * @return int
     */
    function ticks_to_timestamps($ticks)
    {
        $timestamp = ((double)$ticks / 10000000) - 62135596800;
        return (int)$timestamp;
    }
}

if(!function_exists('timestamps_to_ticks')) {

    /**
     * @param $ticks
     * @return int
     */
    function timestamps_to_ticks($timestamps)
    {
        $timestamp = ($timestamps + 62135596800) * 10000000 ;
        return (int)$timestamp;
    }
}

if(!function_exists('is_holiday')) {

    /**
     * @param string|Carbon|Verta $date
     * @return bool
     */
    function is_holiday($date)
    {
        try {
            if(is_null($date)) {
                return false;
            }

            $date = Verta::parse($date)->format('Y-m-d');

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL             => "https://pholiday.herokuapp.com/date/" . $date,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 30,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_SSL_VERIFYPEER  => false,
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err)
                return false;

            $response = json_decode($response);

            return $response->isHoliday;
        } catch (Exception $exception) {
            return false;
        }
    }
}

if(!function_exists('is_not_holiday')) {

    /**
     * @param string|Carbon|Verta $date
     * @return bool
     */
    function is_not_holiday($date)
    {
        return !is_holiday($date);
    }
}
//
//if(!function_exists('global_collective_config')) {
//
//    /**
//     * @return \Modules\Backend\Entities\Setting
//     */
////    function global_collective_config()
////    {
////        if (Cache::has(CacheKey::GLOBAL_COLLECTIVE_SETTING)) {
////            return Cache::get(CacheKey::GLOBAL_COLLECTIVE_SETTING);
////        }
////
////        $setting = \Modules\Backend\Entities\Setting::where('slug', 'collective')->first();
////
////        Cache::put(CacheKey::GLOBAL_COLLECTIVE_SETTING, $setting, 14400);
////
////        return $setting;
////    }
//}

//if(!function_exists('clear_global_collective_config_cache')) {
//    function clear_global_collective_config_cache()
//    {
//        Cache::forget(CacheKey::GLOBAL_COLLECTIVE_SETTING);
//    }
//}
//
//if(!function_exists('ir_timezone_offset')) {
//
//    /**
//     * @return string
//     */
//    function ir_timezone_offset()
//    {
//        if (date('I', carbon()->timestamp)) {
//            return '+04:30';
//        } else {
//            return '+03:30';
//        }
//    }
//}
if (!function_exists('fractal')) {

    function fractal($data, Transformer $transformer): Fractal
    {
        return new Fractal($data, $transformer);
    }
}

if(!function_exists('hashid')) {
    /**
     * Convert id to hashis and reverse
     *
     * @param string|array $string
     * @return array|string|integer|bool
     */
    function hashid($param)
    {
        if(is_numeric($param)) {
            $out = Hashids::encode((int)$param);
            if(is_numeric($out)) {
                $out = 'h' . (string)$out;
            }

            return $out;
        }

        if(is_string($param)) {
            $decoded = Hashids::decode((string)$param);

            $out = is_null($decoded) || empty($decoded) ? 0 : $decoded[0];
            if ($out == 0 && str_starts_with($param, 'h') && is_numeric(substr($param, 1))) {
                $decoded = Hashids::decode((string)substr($param, 1));

                $out = is_null($decoded) || empty($decoded) ? 0 : $decoded[0];
            }
            return $out;
        }

        if(is_array($param)) {
            return array_map(function($item) {
                return hashid($item);
            }, $param);
        }

        return false;
    }
}

if(!function_exists('is_hashid')) {
    /**
     * @param string $string
     *
     * @return bool
     * @throws \Exception
     */
    function is_hashid($string)
    {
        return !( is_numeric($string) || empty(hashid($string)) );
    }
}

