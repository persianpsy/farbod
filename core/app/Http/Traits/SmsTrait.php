<?php


namespace App\Http\Traits;
use App\Http\ApiResponse;
use Illuminate\Support\Facades\Mail;
use Kavenegar;


trait SmsTrait
{
    public function SendSms($receptor,$message)
    {
        try{
            $result = Kavenegar::Send('10008663',$receptor,$message);
            if($result){
                foreach($result as $r){
                    return $r->status;
                }
            }
            return $result;
        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            return false;
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            return false;
        }
    }
    public function SendEmailAuthCode($email,$code){
        try {
            $details = [
                'code' => $code,
            ];
            Mail::to($email)->send(new \App\Mail\otpMail($details));
            return true;
        }catch (\Exception $exception){
            return false;
        }
    }
    public function SendAuthCode($receptor,$template,$param_one = null,$param_two = null,$param_three = null)
    {
        try{
            $result = Kavenegar::VerifyLookup($receptor,$param_one,$param_two,$param_three,$template);
             if($result){
                 foreach($result as $r){
                     return $r->status;
                 }
             }

        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            return false;
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            return false;
        }

        return $result;

    }

    public function generateCode(){
   //return 000000 ;
        return rand(100000,999999);
//        $characters = '0123456789';
//        $charactersLength = strlen($characters);
//        $length = 6;
//        $randomString = '';
//        for ($i = 0; $i < $length; $i++) {
//            $randomString .= $characters[rand(0, $charactersLength - 1)];
//        }
//        return $randomString;
    }
}

