<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Repositories\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\String\u;

class AnswerController extends Controller
{
    protected $model;
    public function __construct(Answer $answer)
    {
        $this->model =  new Repository($answer);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Answer $answer = null)
    {
        $conditions = array();
        if ($answer)
            $conditions['id'] = $answer->id;

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
        $user = Auth::guard('api')->user();
        if (!$user)
            return response()->json(['msg'=>'شما اجازه انجام عملیات را ندارید'],419);
        $data = $request->only($this->model->getModel()->fillable);
        $data['user_id'] = $user->id;
        return $this->model->create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function show(Answer $answer)
    {
        return $this->model->show($answer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Answer $answer)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $answer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Answer $answer)
    {
        return $this->model->delete($answer);
    }
}
