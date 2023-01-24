<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Setting;
use App\Repositories\Repository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $model;
    public function __construct(Setting $setting)
    {
        $this->model =  new Repository($setting);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Setting $setting = null)
    {
        $conditions = array();
        if ($setting)
            $conditions['id'] = $setting->id;

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
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting)
    {
        return $this->model->show($setting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $setting);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting)
    {
        return $this->model->delete($setting);
    }

    public function getAdminDashboard(Request $request)
    {
        $last_day = Reservation::where('status',1)->where('created_at','>=',Carbon::now()->subDays(1))->get()->sum('price');
        $month_day_reservations = Reservation::where('status',1)->where('created_at','>=',Carbon::now()->subDays(30))->get()->sum('price');
        $toman_reservations = Reservation::where('status',1)->whereHas('payment',function ($q){
            $q->where('gateway','zarinpal');
        })->get()->sum('price');
        $paypal_reservations = Reservation::where('status',1)->whereHas('payment',function ($q){
            $q->where('gateway','paypal');
        })->get()->sum('price');
        return response(['last_day_price'=>$last_day,'month_price'=>$month_day_reservations,'toman_price'=>$toman_reservations,'dollar_price'=>$paypal_reservations]);
    }
}
