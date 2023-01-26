<?php

namespace App\Http\Controllers;

use App\Models\CategoryStaff;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Repository;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Traits\SmsTrait;

class StaffController extends BaseController
{
     use SmsTrait;
    protected $model;
    public function __construct(Staff $staff)
    {
        $this->model =  new Repository($staff);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Staff $staff = null)
    {

        $conditions = array();
        if ($staff)
            $conditions['id'] = $staff->id;
    
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
        }
          
      
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
      
        $user = new User();
        $user->location   = 0;
        $user->first_name = $request->first_name;
        $user->last_name  = $request->last_name;
        $user->en_first_name = $request->en_first_name;
        $user->en_last_name = $request->en_last_name;
        $user->cellphone = $request->cellphone;
        
        
        $user->save();
        
        $staff = new Staff();
        $staff->cost_toman = $request->cost_toman;
        $staff->user_id = $user->id;
        $staff->description = $request->description;
        $staff->en_description = $request->en_description;
        $staff->cost_dollar = $request->cost_dollar;
        $staff->commission = $request->commission;
        $staff->en_commission = $request->en_commission;
        $staff->time_to_visit = $request->time_to_visit;
        $staff->en_experience = $request->en_experience;
        $staff->experience = $request->experience;
        $staff->en_degree = $request->en_degree;
        $staff->degree = $request->degree;
        $staff->en_aboutme = $request->en_aboutme;
        $staff->aboutme = $request->aboutme;
        $staff->plan = $request->plan;
        $staff->licence = $request->licence;
        $staff->is_doctor = $request->is_doctor;
        $staff->rating = $request->rating;
        $staff->sheba = $request->sheba;
        $staff->save();
        
        
         $wallet = new Wallet();
         $wallet->user_id = $user->id;
         $wallet->currency ='0';
         $wallet->save();
         
         $wallet2 = new Wallet();
         $wallet2->user_id = $user->id;
         $wallet2->currency ='1';
         $wallet2->save();
         
        $res = $this->SendAuthCode($request->cellphone,'firstsms', explode(" ",  $request->last_name)[0]);

        // if ($request->category){
        //     $categories = json_decode($request->category,true);
        //     foreach ($categories as $category){
        //         CategoryStaff::create([
        //             'category_id'=>$category->category_id,
        //             'staff_id'=>$staff->id
        //         ]);
        //     }
        // }
        

        return $this->handleResponse(['id' => $staff->id,'res' => $res],'saved doctor !');
    }
    
    public function storeImage(Request $request)
    {
        $staff = Staff::with('user')->latest()->first();
        if ($request->has('image')) {
            $destination_path ='/staff/image';
            $image_name = Verta::now()->format('Hmjs').rand(0,1000000);
            $path = $request->image->storeAs($destination_path,$image_name,"public");
            $staff->image     = env('APP_URL').'/public/storage/'.$path;
            $staff->user->avatar = env('APP_URL').'/public/storage/'.$path;
            $staff->user->save();
        }

        if ($request->has('cover')) {
            $destination_path ='/staff/cover';
            $image_name = Verta::now()->format('Hmjs').rand(0,1000000);
            $path = $request->cover->storeAs($destination_path,$image_name,"public");
            $staff->cover     = env('APP_URL').'/public/storage/'.$path;
        }
        $staff->save();
        
         return $this->handleResponse( $staff,'saved doctor !');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Http\Response
     */
    public function show(Staff $staff)
    {
        return $this->model->show($staff);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Staff $staff)
    {
        $staffData = $request->only($this->model->getModel()->fillable);
        $newStaff = $this->model->update($staffData,$staff);
        return $newStaff;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Staff  $staff
     * @return \Illuminate\Http\Response
     */
    public function destroy(Staff $staff)
    {
        return $this->model->delete($staff);
    }
    public function showReservation(Request $request)
    {
        $staff = Staff::query()->where('user_id',$request->user()->id)->first();
        if($staff)
        {
            $this->handleResponse(['data' => $staff,'count' => $staff->count()],'have reservation!');
        }
        $this->handleError([],'not have reservation');
    }

    public function showRialWallet(Request $request)
    {
        $wallet = Wallet::query()->where('id',$request->user()->id)->where('currency',1)->first();
        if($wallet)
        {
            $this->handleResponse($wallet,'have wallet!');
        }
        $this->handleError([],'not have wallet');
    }
}
