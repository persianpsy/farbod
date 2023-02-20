<?php namespace App\Repositories;

use App\Http\Controllers\BaseController;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Payment;
use App\Transformers\PaymentTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment as Pay;
use Kavenegar;
use Illuminate\Support\Facades\Redirect;

class PaymentRepository extends BaseController
{
    private function createToken($price,User $user,$callback,$data=[]){
        $crypt_price = md5($price);
        $crypt_user_id = md5($user->id);
        $token = $crypt_user_id.Str::random(10).$crypt_price;

        return $token;
    }

    public function newPayment($price,User $user,$callback = null,$data=[],$is_online = false)
    {
        $payment = New Payment();
        $payment->user_id = $user->id;
        $payment->price = $price;
//        if ($is_online)
//            $payment->token = $this->createToken($price,$user,$callback,$data);
        $payment->save();
        return $payment;
    }

    public function newRequest($price,$date,User $user,$callback = null,$data=[],$is_online = false)
    {
        $payment = New Payment();
        $payment->user_id = $user->id;
        $payment->price = $price;
        $payment->type = 1;
        $payment->status =3;
        $payment->payment_date = $date;
        if ($is_online)
            $payment->token = $this->createToken($price,$user,$callback,$data);
        $payment->save();
        return $payment;
    }

    public function pay($token)
    {
        $payment = Payment::where('token',$token)->first();
        $url="https://panel.persianpsychology.com/dashboard/".$payment->id;
        if ($payment){
            return Pay::callbackUrl($url)->purchase(
                (new Invoice)->amount(intval($payment->price)),
                function($driver, $transactionId) use ($payment){
                    $payment->update([
                        'transaction_id' => $transactionId
                    ]);
                }
            )->pay()->render();
        }else{
            return response()->json(['پرداخت شما نامعتبر است']);
        }
    }

    public function jsonPay($token,$via,$user)
    {
        $payment = Payment::where('token',$token)->first();

        if (!$payment){
            return response()->json(['پرداخت شما نامعتبر است'],400);
        }
        $description = serialize([
            'event' => 'ورود به درگاه بانک',
            'phone' => $user->cellphone
        ]);
        activity()->causedBy(Auth::user())->log($description);
        $url=route('payment.verify',[$payment->id]);

            //decharge wallet
            // $invoice = new Invoice();
            // $invoice->amount($payment->price);
            // $payByBank = Pay::callbackUrl($url);
            // $payByBank->config('description','رزرو جلسه دکتر ');

            // $payByBank->purchase($invoice,function ($driver,$transactionid) use ($payment) {
            //     $payment->update([
            //         'transaction_id' => $transactionid
            //     ]);
            // });
            $data = array("merchant_id" => 'ebfe5aa5-a483-4614-85d3-ab608471254a',
            "amount" => $payment->price,
            "callback_url" => $url,
            "description" => 'سیترون',
            // "metadata" => ["mobile" => Auth::user()->phone],
            // "wages" => [
            //     [
            //       "iban" => "IR720620000000201005097002",
            //       "amount" => (int)($payment->price)/2,
            //       "description" => "تسهیم سود فروش  به شرکت"
            //     ]
            // ]
        );
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);

        if ($err) {
           return $result;
        } else {
            if (empty($result['errors'])) {
                if ($result['data']['code'] == 100) {

                    return  "https://www.zarinpal.com/pg/StartPay/" . $result['data']["authority"];
                   // header('Location: https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);
                }
            } else {
                // echo 'Error Code: ' . $result['errors']['code'];
                // echo 'message: ' . $result['errors']['message'];
                //  return 'ddd';

            }
        }
        return $result ;
            // return $payByBank->pay();
//            return Pay::via($via)->callbackUrl($url)->purchase(
//                (new Invoice)->amount(intval($payment->price)),
//                function($driver, $transactionId) use ($payment){
//                    $payment->update([
//                        'transaction_id' => $transactionId
//                    ]);
//                }
//            )->pay()->toJson();
//        }else{
//            return response()->json(['پرداخت شما نامعتبر است']);
//        }
    }

    public function verify($transaction_id,Payment $payment)
    {
        $user = $payment->user;

        try {

            // You can show payment referenceId to the user.
            $payment->update([
                // 'ref_id'=>$receipt->getReferenceId(),
                'status'=>4
            ]);

//            $reservation = Reservation::where('payment_id',$payment->id)->where('user_id',$user->id)->first();
//            dd($reservation);
//            $reservation->update([
//                'status'=>1
//            ]);
            //$emailData = [
                //'location'=>$reservation->location,
                //'full_name'=>$user->full_name,
              //  'datetime'=>$reservation->appointment->datetime
            //];
            //Mail::to($user->email)->send(new \App\Mail\completeReservation//($emailData));

            //$last_day_reminder = Carbon::parse($reservation->appointment->datetime)->subDay();
            //Mail::to($user->email)->later(Carbon::now()->diffInSeconds($last_day_reminder),new \App\Mail\lastDayReminder($emailData));

            //$last_two_hour_reminder = Carbon::parse($reservation->appointment->datetime)->subHours(2);
            //Mail::to($user->email)->later(Carbon::now()->diffInSeconds($last_two_hour_reminder),new \App\Mail\twoHoursReminder($emailData));

//            $confirm_reserve_message = "Persian Psychology
//جلسه شما برای روز ".
//                $reservation->appointment->datetime
//                ." نهایی شد.
//با سپاس از پرداخت شما.
//در مسیر جدید همراه تان هستیم!
//";
//            $result = Kavenegar::Send('10008663', $user->cellphone, $confirm_reserve_message);
//
//
//            if ($reservation->location === 'Iran') {
//                $message = "
//                Persian Psychology
//                یادآوری جلسه مشاوره"
//                    . $user->full_name .
//                    "عزیز، جلسه شما در "
//                    . $reservation->appointment->datetime .
//                    "می باشد.
//            ";
//
//                $message2 = "
//                Persian Psychology
//                یادآوری جلسه مشاوره".
//                    $user->full_name
//                    ." عزیز، جلسه شما ".
//                    $reservation->appointment->datetime
//                    ."می باشد.
//برای برگزاری جلسه مجازی وارد حساب کاربری خود شوید.";
//
//            }else{
//                $message = "Persian Psychology
//Session reminder
//Dear ".$user->full_name." your session is on ".$reservation->appointment->datetime.".
//";
//
//                $message2 = "Persian Psychology
//Session reminder
//Dear ".$user->full_name." your session is at ".$reservation->appointment->datetime.".
//For virtual call, sign in to your account.
//";
//            }
//            $job = (new \App\Jobs\smsReminder($user->cellphone, $message))->delay($last_day_reminder);
//            dispatch($job);
//
//            $job = (new \App\Jobs\smsReminder($user->cellphone, $message2))->delay($last_two_hour_reminder);
//            dispatch($job);


            return $this->handleResponse( $payment->only(['transaction_id','ref_id','price','updated_at']),'okay payment!');

            //return response()->json(['message'=>'پرداخت شما با موفقیت انجام شده است.','code'=>200,'payment'=>$payment->only(['transaction_id','ref_id','price','updated_at']),'model'=>$payment->model]);

        } catch (InvalidPaymentException $exception) {
            /**
            when payment is not verified, it will throw an exception.
            We can catch the exception to handle invalid payments.
            getMessage method, returns a suitable message that can be used in user interface.
             **/
            //should be completed
        //   return $this->handleError('error' ,[]);
           return response()->json(['message'=>$exception->getMessage(),'code'=>$exception->getCode(),'payment'=>$payment->only(['transaction_id','ref_id','price','updated_at']),'model'=>$payment->model]);
        }
    }

    public function get($id)
    {
        $data = Payment::orderBy('id', 'DESC')->where('user_id',$id)->get();

        if ($data)
        {
            return $this->handleResponse( fractal($data, new PaymentTransformer())->transform(),'show successfully !');
        }

        return $this->handleError([],'');
    }

}
