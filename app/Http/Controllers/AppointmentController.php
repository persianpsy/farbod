<?php

namespace App\Http\Controllers;

use App\Classes\AppointmentStatus;
use App\Http\Requests\IndexAppointmentUserRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Staff;
use App\Repositories\Repository;
use Carbon\Carbon;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends BaseController
{
    protected $model;
    public function __construct(Appointment $appointment)
    {
        $this->model =  new Repository($appointment);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexAppointmentUserRequest $request,Appointment $appointment = null)
    {
        
        $data = Staff::with('user');
        $name = explode("-", $request->staff_id);
        $user_id = User::query()->where('en_first_name',$name[0])->where('en_last_name',$name[1])->first()->id;
      $user_id = Staff::query()->where('user_id',$user_id)->first()->id;
            $data->where('id',$user_id);
      
        if ($request->month ) {
            $time =  \verta()->month($request->month)->startMonth();
              if(\verta()->month>$request->month)
                {
                    $passt= 'all';
                } else {
                    $passt  =  \verta()->day;
                }
            $appointment = DB::table('appointments')
                ->where('deleted_at',null)
                ->where('date','>=', \verta()->month($request->month)->startMonth())
                 ->where('date','>',\verta()->yesterday()->formatDate())
                ->where('date','<',\verta()->month($request->month)->endMonth())
                ->where('staff_id',$user_id)
                ->where('date','>=', Verta::now())
                ->where('status',AppointmentStatus::ACTIVE)
            ;

                $start = \verta()->month($request->month)->startMonth();
                $selected = false;
                if (DB::table('appointments')
                 ->where('deleted_at',null)
                    ->where('date','=', $start->format('Y-m-d'))
                    ->where('staff_id',$user_id)
                    ->where('status',AppointmentStatus::ACTIVE)->first())
                {
                    $selected = true;
                }

                $date[0] = array( $start->format('Y-m-d'),$selected);
                
            for ($i=1;$i < 30;$i++ )
            {
                $start = \verta()->month($request->month)->startMonth();
                $d = $start->addDays($i);
                $selected = false;
                if (DB::table('appointments')
                 ->where('deleted_at',null)
                    ->where('date','>=', $start->format('Y-m-d'))
                    ->where('date','<',\verta()->month($request->month)->endMonth())
                    ->where('staff_id',$user_id)
                     ->where('status',AppointmentStatus::ACTIVE)->where('date',$d->format('Y-m-d'))->first())
                {
                    $selected = true;
                }
                
                

                $date[$i] = array( $d->format('Y-m-d'),$selected);
            }

        } else {
            $time =  \verta()->startMonth();
            $appointment = DB::table('appointments')
                 ->where('deleted_at',null)
                ->where('date','>=', \verta()->startMonth())
                ->where('date','>',\verta()->now())
                 ->where('date','>',\verta()->yesterday()->formatDate())
                ->where('date','<',\verta()->endMonth())
                ->where('staff_id',$user_id)
                ->where('status',AppointmentStatus::ACTIVE)
            ;
              $passt  =  \verta()->day;
                    
                
                $start = \verta()->month($request->month)->startMonth();
                $selected = false;
                if (DB::table('appointments')
                    ->where('deleted_at',null)
                    ->where('date','=', $start->format('Y-m-d'))
                    ->where('staff_id',$user_id)
                    ->where('status',AppointmentStatus::ACTIVE)->first())
                {
                    
                    $selected = true;
                }

                $date[0] = array( $start->format('Y-m-d'),$selected);


            for ($i=1;$i < 30;$i++ )
            {
                $start = \verta()->startMonth();
                $d = $start->addDays($i);
                // $d = new Verta( $i.'day') ;
                // if($d > $time->endMonth())
                // {
                //     break ;
                // }
                $selected = false;
                if (DB::table('appointments')
                    ->where('deleted_at',null)
                    ->where('date','>=',\verta()->month($request->month)->startMonth())
                    ->where('date','<',\verta()->endMonth())
                    ->where('staff_id',$user_id)
                     ->where('status',AppointmentStatus::ACTIVE)
                    ->where('date',$d->format('Y-m-d'))->first())
                {
                    $selected = true;
                }
                $date[$i] = array( $d->format('Y-m-d'),$selected);

            }
        }
        $meeting = $appointment->get(['time','date','id'])->toArray();
        
      
        
        $res = [
            'appointment' => $meeting,
            'year'   =>  $time->year,
            'month'  =>  $request->month ?: $time->month ,
            'day'    =>  $time->day,
            'rooz'   =>  $time->dayOfWeek,
            'today'  =>  $time->today()->format('Y-m-d'),
            'en'     =>  Carbon::now()->format('Y-m-d'),
            'start'  =>  $time->dayOfWeek,
            'date'   =>  $date,
            'passt'  => $passt,
            'name'  => $data->first()->user->full_name,
            'en_name' => $data->first()->user->en_full_name
        
        ];

          return $this->handleResponse($res,'ok');
    }
    public function panelIndex(Request $request,Appointment $appointment = null)
    {
        $conditions = array();
        if ($appointment)
            $conditions['id'] = $appointment->id;

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
     */
    public function store(StoreAppointmentRequest $request)
    {
        $staff = Staff::where('user_id',$request->user()->id)->first();
        $data = Appointment::where('staff_id',$staff->id)->where('date',$request->date)->whereBetween ('time',[\verta($request->time)->subMinutes(45)->format('H:i'),\verta($request->time)->addMinutes(45)->format('H:i')])->first();
        
        if($data){
              return $this->handleError('جلسه دیگری نزدیک این تاریخ ثبت شده است',$data);
        }
        $appointment = new Appointment ;
        $appointment->staff_id = $staff->id;
        $appointment->date = $request->date;
        $appointment->time = $request->time;
        $appointment->save();

        return $this->handleResponse([],'جلسه با موفقیت ثبت شد');
    }
        public function storeAdmin(Request $request)
    {

          
        $appointment = new Appointment ;
        $appointment->staff_id = $request->staff;
        $appointment->date = $request->date;
        $appointment->time = $request->time;
        $appointment->save();

        return $this->handleResponse($appointment,'ok!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment)
    {
        return $this->model->show($appointment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appointment $appointment)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $appointment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appointment $appointment)
    {
        return $this->model->delete($appointment);
    }
    public function doctorAppointment(Request $request)
    {
        
        $staff_id = Staff::query()->where('user_id',$request->user()->id)->first();
        if($request->up){
            return Appointment::query()->where('staff_id',$staff_id->id)->orderBy('date','ASC')->where('date','>',\verta()->yesterday()->formatDate())->get();
        } else {
            return Appointment::query()->where('staff_id',$staff_id->id)->orderBy('date','desc')->where('date','>',\verta()->yesterday()->formatDate())->get();
        }
       
    }
    
    public function removeItem(Request $request)
    {
        $data = Appointment::query()->where('id',$request->id)->delete();
    }
    
     public function indexTotal (Request $request,Appointment $appointment = null)
    {
        
        if ($request->month ) {
            $time =  \verta()->month($request->month)->startMonth();
            
            $appointment =  Appointment::with('staff')
                ->where('date','>=', \verta()->month($request->month)->startMonth())
                      ->where('status',AppointmentStatus::ACTIVE)
                ->where('date','<',\verta()->month($request->month)->endMonth())
               
             
            ;

                $start = \verta()->month($request->month)->startMonth();
                $selected = false;
                if (Appointment::query()
                    ->where('date','=', $start->format('Y-m-d'))
                          ->where('status',AppointmentStatus::ACTIVE)
                   ->first()
                    )
                {
                    $selected = true;
                }

                $date[0] = array( $start->format('Y-m-d'),$selected);
                
            for ($i=1;$i < 30;$i++ )
            {
                $start = \verta()->month($request->month)->startMonth();
                $d = $start->addDays($i);
                $selected = false;
                if (DB::table('appointments')
                    ->where('date','>=', $start->format('Y-m-d'))
                          ->where('status',AppointmentStatus::ACTIVE)
                    ->where('date','<',\verta()->month($request->month)->endMonth())
                  
                     ->where('date',$d->format('Y-m-d'))->first())
                {
                    $selected = true;
                }

                $date[$i] = array( $d->format('Y-m-d'),$selected);
            }

        } else {
            $time =  \verta()->startMonth();
            $appointment = Appointment::with('staff')
                ->where('date','>=', \verta()->startMonth())
                ->where('date','<',\verta()->endMonth())
                ->where('date','>',\verta()->now())
                      ->where('status',AppointmentStatus::ACTIVE)
            
            ;
            
    
                $start = \verta()->month($request->month)->startMonth();
                $selected = false;
                if  (Appointment::query()
                    ->where('date','=', $start->format('Y-m-d'))
                    ->where('date','>',\verta()->now())
                    ->where('status',AppointmentStatus::ACTIVE)
                   ->first()
                    )
                {
                    $selected = true;
                }

                $date[0] = array( $start->format('Y-m-d'),$selected);


            for ($i=1;$i < 30;$i++ )
            {
                $start = \verta()->startMonth();
                $d = $start->addDays($i);
                // $d = new Verta( $i.'day') ;
                // if($d > $time->endMonth())
                // {
                //     break ;
                // }
                $selected = false;
                if (DB::table('appointments')
                    ->where('date','>=',\verta()->month($request->month)->startMonth())
                    ->where('date','<',\verta('+1 month'))
                          ->where('status',AppointmentStatus::ACTIVE)
                    ->where('date',$d->format('Y-m-d'))->first())
                {
                    $selected = true;
                }
                $date[$i] = array( $d->format('Y-m-d'),$selected);

            }
        }
        $meeting = $appointment->get()->toArray();

        
        $data = [
            'appointment' => $meeting,
            'year'   =>  $time->year,
            'month'  =>  $request->month ?: $time->month ,
            'day'    =>  $time->day,
            'rooz'   =>  $time->dayOfWeek,
            'today'  =>  $time->today()->format('Y-m-d'),
            'en'     =>  Carbon::now()->format('Y-m-d'),
            'start'  =>  $time->dayOfWeek,
            'date'   =>  $date,
        
        ];

          return $this->handleResponse($data,'ok');
    }
}
