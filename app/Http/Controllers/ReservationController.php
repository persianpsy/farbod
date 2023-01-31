<?php

namespace App\Http\Controllers;

use App\Classes\AppointmentStatus;
use App\Classes\ReservationStatus;
use App\Http\Requests\CreateReservationRequest;
use App\Http\Requests\DiscountReservationRequest;
use App\Http\Requests\getInfoReservationRequest;
use App\Http\Requests\SubmitReservationRequest;
use App\Models\Appointment;
use App\Models\Coupon;
use App\Models\Reservation;
use App\Http\Traits\SmsTrait;
use App\Models\Staff;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\PaymentRepository;
use App\Repositories\Repository;
use App\Transformers\ReservationInfoTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JoisarJignesh\Bigbluebutton\Facades\Bigbluebutton;

class ReservationController extends BaseController
{
    use SmsTrait;
    protected $model;
    protected $paymentRepository;

    public function __construct(Reservation $reservation,PaymentRepository $paymentRepository)
    {
        $this->model =  new Repository($reservation);
        $this->paymentRepository = $paymentRepository;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Reservation $reservation = null)
    {
         $id = \App\Models\Staff::query()->where('user_id',$request->user()->id)->first()->id;
          return \App\Models\Reservation::with('appointment')->whereIn('status',[2,4,5,6])->whereHas('appointment',function ($q) use($id) {
              $q->where('staff_id' ,$id);
            })->get();


    }

     public function adminInfo(Request $request)
    {
        //  $model = $this->model;

        //     $model = $model->with('appointment','user','staff','appointment.staff');
        //     $model->orderBy('created_at', 'DESC');
        //     return $model->paginate();

        return \App\Models\Reservation::with('appointment','user','staff')->get()->pagiante(30);



    }


     public function indexUser(Request $request,Reservation $reservation = null)
    {
        $id = null;
          return \App\Models\Reservation::with('appointment','satff')->whereIn('status',[2,4,5,6])->whereHas('appointment',function ($q) use($id) {
              $q->where('user_id' ,$request->user()->id);
            })->get();


    }
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateReservationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateReservationRequest $request)
    {

        $wallet = Wallet::with('user')->where('user_id',$request->user()->id)->first();
        if (!$wallet) {
            return $this->handleError([],'not ok wallet!');
        }

        $appointment = Appointment::where('id',$request->appointment_id)->with('staff')->firstOrFail();



        if($request->dollar)
        {

            $amount = $appointment->staff->cost_dollar;

        } else {
              if (!$appointment->staff->cost_toman) {
              return $this->handleError([],'not ok for staff!');
             }
             $amount = $appointment->staff->cost_toman;
        }

        $data = [
            'wallet_id'      =>  $wallet->id,
            'user_id'        =>  $request->user()->id,
            'staff_id'       =>  $appointment->staff->id,
            'appointment_id' =>  $request->appointment_id,
            'price'          =>  (int)$amount,
             'status'         =>  ReservationStatus::CREATED
        ];

        $model = $this->model->create($data);


        if($request->dollar)
        {

                          if ($appointment->staff->cost_dollar>$wallet->amount) {
            return $this->handleError('not ok for wallet!',['id'=>$model->id,'price' => (int)$appointment->staff->cost_dollar - (int)$wallet->amount ]);
                          }
                           $wallet->update([
            'amount' => (int)$wallet->amount - (int)$appointment->staff->cost_dollar
        ]);

        } else {
                  if ($appointment->staff->cost_toman>$wallet->amount) {
            return $this->handleError('not ok for wallet!',['id'=>$model->id,'price' => (int)$appointment->staff->cost_toman - (int)$wallet->amount ]);
            }
             $wallet->update([
            'amount' => (int)$wallet->amount - (int)$appointment->staff->cost_toman
        ]);

        }

         $appointment->update([
            'status' => AppointmentStatus::INACTIVE
        ]);


        $model->status = ReservationStatus::PAID;
        $model->save();

          $res = $this->SendAuthCode($wallet->user->cellphone,'final',$appointment->date,$appointment->time);



         $staff = Staff::with('user')->where('id',$appointment->staff->id)->first();

          $res_doctor = $this->SendAuthCode($staff->user->cellphone,'doctorreminder',$staff->user->last_name,$appointment->date,$appointment->time);



        return $this->handleResponse([$res,$res_doctor],'okay reservation created');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return Response
     */
    public function show(Reservation $reservation)
    {
        return $this->model->show($reservation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservation  $reservation
     * @return Response
     */
    public function update(Request $request, Reservation $reservation)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $reservation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return Response
     */
    public function destroy(Reservation $reservation)
    {
        return $this->model->delete($reservation);
    }



    public function submit(SubmitReservationRequest $request)
    {
//        $via = 'zarinpal';
        $user = $request->user();
//        if (!$user)
//            return $this->handleError([],'unauthorized!');

        $appointment = Appointment::where('id',$request->appointment_id)->with('staff')->firstOrFail();

        $reserve_data = $request->only($this->model->getModel()->fillable);

        $reserve_data['user_id']=$user->id;
        $reserve_data['appointment_id']=$appointment->id;
        if ($request->lang === 'IR') {
            $via = 'zarinpal';
            $reserve_data['price'] = $appointment->staff->cost_toman;
        } else {
            $via = 'paypal';
            $reserve_data['price'] = $appointment->staff->cost_dollar;
        }
        $reserve_data['status']=4;
        $reserve_data['location']=$request->lang;

        $wallet = Wallet::query()->where('user_id',$request->user()->id)->firstOrFail();
        if (!$wallet) {
            return $this->handleError([],'not ok!');
        }
        if($wallet->amount < $reserve_data['price']){
            return $this->handleError([],'charge wallet!');
        }

        $reserve_data['wallet_id']=$wallet->id;

        try {
            DB::beginTransaction();

            $reserve = $this->model->create($reserve_data);


            $data = [
                'user_id'=>$user->id,
                'price'=>$appointment->staff->cost_toman,
                'model_type'=>'App\Models\Reservation',
                'model_id'=>$reserve->id,
            ];

            $payment = $reserve->payment()->create($data);
            $reserve->update(['payment_id'=>$payment->id]);
            $wallet->update(['amount'=> (int) $wallet->amount - (int) $reserve_data['price'] ]);

            DB::commit();
        }
        catch (\Exception $ex){
            return response($ex,400);
        }
        return $this->handleResponse(['data' => $payment],'okay');
//        return $this->paymentRepository->jsonPay($payment->token,$via);
    }

    public function closest(Request $request)
    {
         $data =  Reservation::with(['appointment','user'])->where('appointment_id',$request->id)->first();

        $data->status = ReservationStatus::FINISHED;


        ///  clean code ??!
        if($data->user->location == '1'){

             $wallet = Wallet::where('user_id',$request->user()->id)
             ->where('currency','0')->first();


        } else {

             $wallet = Wallet::where('user_id',$request->user()->id)
             ->where('currency','1')->first();

        }


        $wallet->amount = (int) $wallet->amount + ( (int) $data->price * ((int)$data->appointment->staff->commission)/100 );



        $wallet->save();


        $data->save();

        $res = $this->SendAuthCode($data->user->cellphone,'vote','کاربر');

        return $this->handleResponse($wallet,'okay');
    }


    public function createRoom(Request $request,Appointment $appointment)
    {

        $reservation = Reservation::with('appointment')->where(['appointment_id'=>$request->id,'status'=>2])->first();

        $url = 'https://www.skyroom.online/skyroom/api/apikey-19196080-5-717552ba3e8e72ccd0c272ee1838cbc6';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, ['json' => [
                "action"=> "createRoom",
                "params"=>[
                    "name"=>"schedule-".$appointment->id.rand(1,100),
                    "title"=> rand(1,10)."جلسه مشاوره رزرو شماره ". $reservation->id,
                    "guest_login"=> true,
                    "max_users"=> 5,
                    "session_duration"=>$reservation->appointment->staff->time_to_visit
                ]
            ]
        ]);
        $content = json_decode($response->getBody());
        if (!$content->ok){
            return response(['msg'=>$content->error_message],419);
        }
        $reservation->room_id = $content->result;
        $reservation->meeting_id = null;
        $reservation->status = 4 ;

        $reservation->Save();

        return response(['msg'=>'اتاق با موفقیت ایجاد شد.','room_id'=>$content->result]);
    }

        public function createMeetingRoom(Request $request,Appointment $appointment)
    {

          $reservation = Reservation::with('appointment.staff.user')->where(['appointment_id'=>$request->id,'status'=>2])->first();
          $room =  \Bigbluebutton::create([
                'meetingID' => $reservation->appointment_id,
                'meetingName' =>  $reservation->appointment_id,
                'moderatorPW' => $reservation->appointment->staff->user->cellphone,
                'endCallbackUrl'  => 'www.persianpsychology.com',
                'logoutUrl' => 'www.persianpsychology.com',
                'attendeePW' => $reservation->user->cellphone,
                'presentation'  => [ //must be array
                    ['link' => 'https://persianpsychology.com/p2.pdf', 'fileName' => 'p2.pdf']
                ],
            ]);


            $join = Bigbluebutton::join([
                    'meetingID' => $reservation->appointment_id,
                    'userName' => 'Doctor',
                    'password' => $reservation->appointment->staff->user->cellphone ,

                 ]);



            $reservation->meeting_id = $reservation->appointment_id ;
             $reservation->room_id = null;
             $reservation->status = '4';
            $reservation->save();

        return $join ;

    }

    public function cleanRoom(Request $request)
    {
          $reservation = Reservation::where('id' , $request->id)->first();

            if($reservation->room_id){
               $reservation->room_id = NULL;
            }
            if( $reservation->meeting_id){
              $reservation->meeting_id = NULL;
            }

            $reservation->status = '2';

            $reservation->update();

            return $reservation;

    }

           public function joinMeetingRoom(Request $request,Appointment $appointment)
    {

          $reservation = Reservation::with('appointment.staff.user')->where(['appointment_id'=>$appointment->id,'status'=>2])->first();


            $join = Bigbluebutton::join([
                    'meetingID' => $reservation->appointment_id,
                    'userName' => 'client'.rand(2,200),
                    'password' => $reservation->appointment->staff->user->cellphone
                 ]);



            $reservation->meeting_id = $reservation->appointment_id ;
             $reservation->room_id = null;
               $reservation->status = '4';
            $reservation->save();

        return $join ;

    }

    public function getRoom(Request $request)
    {
        return 'hi';
        //return \App\Models\Reservation::query()->where('status',4)->where('user_id',$request->user()->id)->first();
    }

    public function joinRoom(Request $request,Appointment $appointment)
    {
        $user = Auth::guard('api')->user();
        if (!$user)
            return response()->json(['کاربر مشخص نشده است'],400);



        $reservation =  Reservation::with('appointment.staff.user')->where('id',$request->id)->whereIn('status',[4,5])->first();

        if(isset($reservation->meeting_id)){


            \Bigbluebutton::create([
                'meetingID' => $reservation->appointment_id,
                'meetingName' =>  $reservation->appointment_id,
                'moderatorPW' => $reservation->appointment->staff->user->cellphone,
                'endCallbackUrl'  => 'www.persianpsychology.com',
                'logoutUrl' => 'www.persianpsychology.com',
                'attendeePW' => $reservation->user->cellphone,
                'presentation'  => [ //must be array
                    ['link' => 'https://persianpsychology.com/p2.pdf', 'fileName' => 'p2.pdf']
                ],
            ]);

            $join = Bigbluebutton::join([
                    'meetingID' => $reservation->appointment_id,
                    'userName' => 'client'.rand(2,200),
                    'password' => $reservation->appointment->staff->user->cellphone
                 ]);
             return response(['msg'=>'آدرس اتصال با موف جلسه شخصی قیت ایجاد شد.','url'=>$join,'mode'=>'psy']);
        }
        $url = 'https://www.skyroom.online/skyroom/api/apikey-19196080-5-717552ba3e8e72ccd0c272ee1838cbc6';
        $client = new \GuzzleHttp\Client();
        $name = 'Doctor';
         $access = 3;
        if($request->client){
            $name = 'Client';
            $access = 2;
        }
        $response = $client->request('POST', $url, ['json' => [
                "action"=> "createLoginUrl",
                "params"=>[
                    "room_id"=>$reservation->room_id,
                    "user_id"=> $user->id,
                    "nickname"=> $name,
                    "access"=> $access,
                    "ttl"=>$reservation->appointment->staff->time_to_visit*60
                ]
            ]
        ]);
        $content = json_decode($response->getBody());
        if (!$content->ok){
            return response(['msg'=>$content->error_message],419);
        }

        return response(['msg'=>'آدرس اتصال با موفقیت ایجاد شد.','url'=>$content->result,'mode'=>'skyroom']);
    }

        public function joinDirectRoom(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user)
            return response()->json(['کاربر مشخص نشده است'],400);


        $reservation =  Reservation::where('room_id',$request->room_id)->whereIn('status',[4,5])->first();

        $url = 'https://www.skyroom.online/skyroom/api/apikey-19196080-5-717552ba3e8e72ccd0c272ee1838cbc6';
        $client = new \GuzzleHttp\Client();
        $name = 'guest';

        $response = $client->request('POST', $url, ['json' => [
                "action"=> "createLoginUrl",
                "params"=>[
                    "room_id"=>$reservation->room_id,
                    "user_id"=> $user->id,
                    "nickname"=> $name,
                    "access"=> 1,
                    "ttl"=>60
                ]
            ]
        ]);
        $content = json_decode($response->getBody());
        if (!$content->ok){
            return response(['msg'=>$content->error_message],419);
        }

        return response(['msg'=>'آدرس اتصال با موفقیت ایجاد شد.','url'=>$content->result]);
    }
        public function voteRoom(Request $request,Appointment $appointment)
    {
        $user = Auth::guard('api')->user();
        if (!$user)
            return response()->json(['کاربر مشخص نشده است'],400);

        if (!$appointment->staff)
            return response('مشاور انتخاب نشده است با پشتیبان تماس بگیرید',419);

        $reservation =  Reservation::where('appointment_id',$appointment->id)->first();

        $reservation->like = $request->like;
        $reservation->comment = $request->comment;
        $reservation->save();



        $appointment->staff->rating = ($appointment->staff->rating+(int)$request->like)/2;

          $appointment->staff->update();

          return $appointment->staff;
         return $this->handleResponse([$reservation,$appointment],'okay');

    }
    public function info(getInfoReservationRequest $request)
    {

        $data = Reservation::query()->where('user_id',$request->user()->id)
            ->whereIn('status', [ReservationStatus::PAID,ReservationStatus::CREATEDROOM,
            ReservationStatus::JOINEDROOM,  ReservationStatus::FINISHED])
            ->get();



        if ($data !== null)
        {

            return $this->handleResponse(fractal($data, new ReservationInfoTransformer())->transform(),'reservation found!');
        } else {

            return  $this->handleError([],'not found reservation !');
        }
    }
}
