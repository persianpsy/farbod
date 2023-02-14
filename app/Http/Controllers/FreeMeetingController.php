<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
// use App\Repositories\Repository;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Classes\AppointmentStatus;
use App\Http\Requests\IndexAppointmentUserRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Staff;
use App\Http\Traits\SmsTrait;
// use App\Repositories\Repository;
use Carbon\Carbon;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class FreeMeetingController extends BaseController
{
    use SmsTrait;
    // protected $model;
    // public function __construct(freeMeeting $freeMeeting)
    // {
    //     $this->model =  new Repository($freeMeeting);
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $user_id = Staff::query()->where('user_id',28)->first()->id;


        $time =  \verta()->startMonth();
        $appointment = DB::table('appointments')
            ->where('deleted_at',null)
            ->where('date','>=',\verta()->today()->formatDate())
            ->where('date','<',\verta()->today()->AddDays(7))
            ->where('staff_id',$user_id)
            ->where('status',AppointmentStatus::ACTIVE)
        ;


        $meeting = $appointment->get(['time','date','id'])->toArray();



        $res = [
            'appointment' => $meeting,
            't1'  =>  $time->today()->format('Y-m-d'),
            't2'  =>  $time->today()->addDay()->format('Y-m-d'),
            't3'  =>  $time->today()->addDays(2)->format('Y-m-d'),
            't4'  =>  $time->today()->addDays(3)->format('Y-m-d'),
            't5'  =>  $time->today()->addDays(4)->format('Y-m-d'),



        ];

          return $this->handleResponse($res,'ok');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $info = $request->user();


        if(!$info){
            return $this->handleError([],'user exist');
        }

        $store = new Reservation();
        $store->appointment_id = $request->id;
        $store->user_id = $info->id;
        $store->price = 0;
        $store->staff_id = 23;
        $store->status = 2;
        $store->save();

        $redis = Redis::connection();



        $data = $redis->get('uuid_'.$request->uuid);



        $redis->set('test_'.$info->id,$data);



        $man = Appointment::where('id',$request->id)->update(['status' => 2]);
        $res = $this->SendAuthCode('00989335192412','requests','رایگان');
        $res = $this->SendAuthCode($request->user()->cellphone,'free',$request->date,$request->time);
        return $this->handleResponse($man,'ok!');
    }

    function storeTest(Request $request){
        $redis = Redis::connection();


        $redis->set('uuid_'.$request->uuid, json_encode([
                'uuid' => $request->uuid,
                'q1' => $request->q1,
                'q2' => $request->q2,
                'q3' => $request->q3,
                'q4' => $request->q4,
                'q5' => $request->q5,
                'q6' => $request->q6,
            ])
        );

        return $this->handleResponse([],'ok!');
    }

     public function adminInfo(Request $request)
    {
        $info = FreeMeeting::with('user','appointment')->get();



        return $this->handleResponse($info,'ok!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\freeMeeting  $freeMeeting
     * @return \Illuminate\Http\Response
     */
    public function show(freeMeeting $freeMeeting)
    {
        return $this->model->show($freeMeeting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\freeMeeting  $freeMeeting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, freeMeeting $freeMeeting)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $freeMeeting);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\freeMeeting  $freeMeeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(freeMeeting $freeMeeting)
    {
        return $this->model->delete($freeMeeting);
    }
}
