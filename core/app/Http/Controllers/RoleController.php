<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Repositories\Repository;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected $model;
    public function __construct(Role $role)
    {
        $this->model =  new Repository($role);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Role $role = null)
    {
        $conditions = array();
        if ($role)
            $conditions['id'] = $role->id;

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
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return $this->model->show($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        return $this->model->delete($role);
    }
}
