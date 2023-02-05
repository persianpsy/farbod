<?php

namespace App\Http\Controllers;

use App\Classes\AppointmentStatus;
use App\Classes\PaymentsStatus;
use App\Classes\ReservationStatus;
use App\Http\Requests\GetPaymentRequest;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\PaymentRepository;
use App\Transformers\PaymentAdminTransformer;
use App\Repositories\Repository;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Wallet;
use App\Http\Traits\SmsTrait;
use Illuminate\Support\Facades\Log;
use App\Exports\PaymentExport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Auth;


class PaymentController extends BaseController
{
     use SmsTrait;
    protected $model;
    protected $paymentRepository;
    public function __construct(Payment $payment,PaymentRepository $paymentRepository)
    {
        $this->model =  new Repository($payment);
        $this->paymentRepository = $paymentRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
     public function indexAdmin(Request $request){

         $model = $this->model;

         $model = $model->with('user');
         $model->orderBy('created_at', 'DESC');
         return ['data'=>$model->paginate(),'total'=>$model->count()];
         $model = $this->model;

            $model = $model->with('user');
            $model->orderBy('created_at', 'DESC');
            return Payment::with('user')->orderBy('created_at', 'DESC')->take(30)->get(); ;
//            return , new PaymentAdminTransformer())->transform();

     }
    public function index(Request $request,Payment $payment = null)
    {
        $conditions = array();
        if ($payment)
            $conditions['id'] = $payment->id;

        if ($request->conditions)
            $conditions = json_decode($request->conditions,true);

        $user = Auth::user();
        if (isset($user)){
            $conditions['user_id'] = $user->id;
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->model->create($request->only($this->model->getModel()->fillable));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $pay = Payment::query()->where('token',$request->route('token'))->first();
        if($pay)
        {
            return $pay;
        }
        return false ;
        return $this->model->show($payment);
    }

      public function sendReceipt(Request $request)
    {
        $pay = Payment::query()->where('token',$request->route('token'))->first();

        if($pay)
        {

             $info = array([

                 "body" => "Welcome to our website! Your Receipt:".$pay->ref_id." A different experience in counseling from all around the worold",
                  "to" => array(["email" => $request->user()->email, "name" => 'client']),
                  "from" => ["email_address_id" => "23758", "name" => "Persian Pschology"],
                  "subject" => "Receipt from Persian Psychology"

            ]);




            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://rest.clicksend.com/v3/email/send',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode($info[0]),
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic YmVoaTgwMEB5YWhvby5jb206QWJjZEAxMjM0NTY='
              ),
            ));

            $res = curl_exec($curl);

            curl_close($curl);

            return $res;
        }
        return false ;
        return $this->model->show($payment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $payment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        return $this->model->delete($payment);
    }

    public function charge(ChargeWalletRequest $request)
    {
        $reservation_id = $request->reservation_id ?: null;
        return 'farbod';

        return (new \App\Repositories\PaymentRepository)->jsonPay($reservation_id);
    }



    public function request(Request $request)
    {
        $user = Auth::user();
        if (!isset($request->price) || !$user)
            return response()->json(['مبلغ مشخص نشده است!'],400);

        return $this->paymentRepository->newRequest($request->price,$request->date,Auth::user(),'',[],true);
    }
    public function pay(Request $request)
    {
        return $this->paymentRepository->pay($request->token);
    }
    public function verify(Request $request,Payment $payment)
    {
        $Authority = $_GET['Authority'];
        $data = array("merchant_id" => 'ebfe5aa5-a483-4614-85d3-ab608471254a', "authority" => $Authority, "amount" => $payment->price);

        $jsonData = json_encode($data);

        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        $reservation = Reservation::with('wallet','user','staff')->where('payment_id',$payment->id)->first();
        $description = [
            'event' => 'verify Bank port ',
            'res'   => $result,
            'reservation' => $reservation

        ];
        activity()->log($description);
//        Log::info('verify payment.', ['payment response' => $result]);

    


       if (isset($result['data']['code']) &&  $result['data']['code']== 100) {

                $referenceId = $result['data']['ref_id'];
                $payment->ref_id = $referenceId ;
                $payment->status =  PaymentsStatus::SUCCESS ;
                $payment->save();
                $wallet = Wallet::where('user_id',$payment->user_id)->first();
                $wallet->amount = (int) $wallet->amount + (int) $payment->price ;
                $wallet->save();

              if($reservation){
                $reservation->appointment->status = AppointmentStatus::INACTIVE;
                $reservation->status = ReservationStatus::PAID ;
                $reservation->appointment->save();
                $reservation->save();
                 $res = $this->SendAuthCode($reservation->user->cellphone,'final',$reservation->appointment->date,$reservation->appointment->time);

                $wallet->amount = (int) $wallet->amount - (int)  $reservation->price ;
                $wallet->save();

               }else {


               }
           return \redirect()->away('https://panel.persianpsychology.com/receipt/'.$payment->token);

         }


        if(isset($result['errors']['code']) && $result['errors']['code']== '-51')
        {
             $payment->update([
              'status'=> PaymentsStatus::FAILED //cancel
            ]);

            if($reservation)
            {
             $reservation->update([
                'status'=>ReservationStatus::CANCEL //cancel
            ]);
            }
            return \redirect()->away('https://panel.persianpsychology.com/receipt/'.$payment->token);

        }

    }

    public function get(GetPaymentRequest $request ,Payment $payment)
    {
        return $this->paymentRepository->get($request->user()->id);
//        $payment = Payment::query()->where('user_id',$request->user()->id)->get();

    }

    public function export()
    {
        return Excel::download(new PaymentExport, 'payment.xlsx');
    }

}
