<?php

namespace App\Http\Controllers;

use App\Http\Requests\CraeteCouponRequest;
use App\Models\Coupon;
use App\Models\User;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use Illuminate\Http\Request;
use Hashids\Hashids;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\SmsTrait;

class CouponController extends BaseController
{
     use SmsTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
       //feature
            return $this->handleResponse(Coupon::all(),'کوپن با موفقیت نمایش داده شد');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
         $hashids = new Hashids();
         $discount = 3 ;
         $profit = 5 ;

        $code =  $hashids->encode($discount.$request->user()->id.$profit);
        $user = User::where('cellphone',$request->cellphone)->firstOrNew();



          $coupon = Coupon::firstOrCreate(array('code' => $code, 'user_id' => $request->user()->id));


            if($coupon->amount < 3 )
            {

                 if($request->cellphone) {
                                        $pieces = explode(" ", $request->user()->first_name);
                    $res = $this->SendAuthCode($request->cellphone,'invite2',$code,$pieces[0],$request->user()->cellphone);

                }


                if($request->email) {

                    $data = ['code' => $code,'text'=>$request->text,'sender'=>$request->sender];

                     $mail =  Mail::to($request->email)->send(new \App\Mail\InviteFriend ($data));

                }


                $coupon->amount =  (int) $coupon->amount  + 1 ;
                $coupon->update();
            }


            return $this->handleResponse([$coupon,$hashids->decode($coupon->code)], 'coupon successfully registered!');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCouponRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCouponRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCouponRequest  $request
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {

    }
      public function discount (Request $request)
    {

//        if($request->code === 'AZAR')
//        {
//             return floor(($request->price*7/10)/10000)*10000;
//        }

         $hashids = new Hashids();
           $coupon = Coupon::query()->where('code',$request->code)->first();



        if (!$coupon){
            return $request->price ;
        }

         $infoo =  substr($hashids->decode($coupon->code)[0],0,strlen($hashids->decode($coupon->code)[0])-1);

            $infooo = substr($infoo,1) ;

       if($request->user()->id == $infooo)
       {

          $coupon->limit =  $coupon->limit + 1 ;
          $coupon->update();

                if($coupon->limit > 3)
            {
                return $request->price ;
            }
          return floor(($request->price*1/2)/10000)*10000 ;
       }
        if($coupon->limit > 3)
        {
            return $request->price ;
        }
        $coupon->limit =  $coupon->limit + 1 ;
        $coupon->update();


        return $request->price - substr($hashids->decode($coupon->code)[0],0,1)*.1*$request->price;

         $coupon ;
    }
}
