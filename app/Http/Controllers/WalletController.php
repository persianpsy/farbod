<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChargeWalletRequest;
use App\Http\Requests\DiscountReservationRequest;
use App\Http\Requests\getInfoWalletRequest;
use App\Models\Coupon;
use App\Models\Wallet;
use App\Models\Appointment;
use App\Repositories\PaymentRepository;
use App\Repositories\Repository;
use App\Models\Reservation;
use App\Transformers\WalletInfoTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends BaseController
{
    protected $model;
    public function __construct(Wallet $wallet)
    {
        $this->model =  new Repository($wallet);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Wallet $wallet = null)
    {
        $conditions = array();
        if ($wallet)
            $conditions['id'] = $wallet->id;

        if ($request->conditions)
            $conditions = json_decode($request->conditions,true);

        $model = $this->model;
        if ($conditions)
            $model = $this->model->list($conditions);

        if ($request->with)
            $model = $model->with($request->with);

        if ($conditions || $request->with){
            if ($request->noPaginate){
                return $model->get();
            }
            return $model->paginate();
        }
        if ($request->noPaginate){
            return $this->model->all();
        }else{
            return $this->model->paginate();
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
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function show(Wallet $wallet)
    {
        return $this->model->show($wallet);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wallet $wallet)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $wallet);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wallet $wallet)
    {
        return $this->model->delete($wallet);
    }

      /**
     * charge a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
       */
    public function charge(ChargeWalletRequest $request)
    {
        $reservation_id = $request->reservation_id ?: null;

        if ($reservation_id) {
           $info = Reservation::query()->where('id',$reservation_id)->first();
        } 
        $price = $request->price ;
        //price
        if(!$price)
       {
           $this->handleError([],'not price info !');
       }

        $payment = (new \App\Repositories\PaymentRepository)->newPayment($price,$request->user(),'',[]);

        if ($reservation_id) {
            $info->update(['payment_id' => $payment->id]);
        }

        return (new \App\Repositories\PaymentRepository)->jsonPay($payment->token,'zarinpal',$request->user()) ;
    }

    public  function discount (DiscountReservationRequest $request)
    {
        $code = Coupon::query()->where('code',$request->code)->first();

        if (!$code)
        {
            return $this->handleError('not found code!',[]);
        }

        $wallet = Wallet::query()->where('id',$request->user()->id)->first();
         if ($wallet->amount)
         {
          $wallet->amount = (100+$code->amount)*($wallet->amount)/100 ;
          $wallet->save();
         }
         return $this->handleResponse($wallet,'wallet improved');
    }

    public function info (getInfoWalletRequest $request)
    {
        $data = Wallet::query()->where('user_id',$request->user()->id)->first();

        if ($data)
        {
            return $this->handleResponse( fractal($data, new WalletInfoTransformer())->transform(),'wallet found!');
        }

        return  $this->handleError('not found wallet !',[]);

    }
    public function DirectPort(Request $request){
        
        $wallet = Wallet::with('user')->where('user_id',$request->user()->id)->first();
        if (!$wallet) {
            return $this->handleError([],'not ok wallet!');
        }

        $appointment = Appointment::where('id',$request->id)->with('staff')->firstOrFail();
        

        $data = [
            'wallet_id'      =>  $wallet->id,
            'user_id'        =>  $request->user()->id,
            'staff_id'       =>  $appointment->staff->id,
            'appointment_id' =>  $request->appointment_id,
            'price'          =>  (int)$request->price,
            'status'         =>  ReservationStatus::CREATED
        ];

        $model = $this->model->create($data);
        
        $payment = (new \App\Repositories\PaymentRepository)->newPayment($request->price,$request->user(),'',[]);

      
        $model->update(['payment_id' => $payment->id]);
       

        return (new \App\Repositories\PaymentRepository)->jsonPay($payment->token,'zarinpal',$request->user()) ;

    }
    
}
