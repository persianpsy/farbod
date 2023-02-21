<?php

namespace App\Http\Controllers;

use App\Models\RequestTherapy;
use App\Repositories\Repository;
use App\Models\Staff;
use App\Http\Traits\SmsTrait;
use App\Models\User;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestTherapyController extends BaseController
{
     use SmsTrait;
    protected $model;
    public function __construct(RequestTherapy $requestTherapy)
    {
        $this->model =  new Repository($requestTherapy);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,RequestTherapy $requestTherapy = null)
    {

          $text = RequestTherapy::with('user','staff','staff.user')->where('status','=',0)->get();


         return $this->handleResponse( $text,'saved request !');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $text = new RequestTherapy();

        $name = explode("-", $request->staff_id);
        $user_id = User::query()->where('en_first_name',$name[0])->where('en_last_name',$name[1])->first()->id;
        $id = Staff::query()->where('user_id',$user_id)->first()->id;

        $text->request = $request->text;
        $text->user_id = $request->user()->id;
        $text->staff_id = $id;

        $text->save();
        $description = serialize([
            'event' => 'درخواست جلسه',
            'time' =>  Carbon::now()
        ]);
        activity()->causedBy(Auth::user())->log($description);

         $res = $this->SendAuthCode('00989335192412','requests','فوری');

        return $this->handleResponse( $text,'saved request !');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RequestTherapy  $requestTherapy
     * @return \Illuminate\Http\Response
     */
    public function show(RequestTherapy $requestTherapy)
    {
        return $this->model->show($requestTherapy);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RequestTherapy  $requestTherapy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

       $id = $request->route('id');

       $info = RequestTherapy::where('id',$id)->first();
       $info->status = 1;
       $info->save();

        return $this->handleResponse( [],'updated request !');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RequestTherapy  $requestTherapy
     * @return \Illuminate\Http\Response
     */
    public function destroy(RequestTherapy $requestTherapy)
    {
        return $this->model->delete($requestTherapy);
    }

    public function list(Request $request)
    {
        $id = Staff::query()->where('user_id',$request->user()->id)->first()->id;

          $text = RequestTherapy::where('staff_id',$id)->get();


         return $this->handleResponse( $text,'saved request !');
    }
}
