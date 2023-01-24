<?php

namespace App\Http\Controllers;

use App\Models\CategoryStaff;
use App\Repositories\Repository;
use Illuminate\Http\Request;

class CategoryStaffController extends Controller
{
    protected $model;
    public function __construct(CategoryStaff $categoryStaff)
    {
        $this->model =  new Repository($categoryStaff);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,CategoryStaff $categoryStaff = null)
    {
        $conditions = array();
        if ($categoryStaff)
            $conditions['id'] = $categoryStaff->id;

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
     * @param  \App\Models\CategoryStaff  $categoryStaff
     * @return \Illuminate\Http\Response
     */
    public function show(CategoryStaff $categoryStaff)
    {
        return $this->model->show($categoryStaff);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CategoryStaff  $categoryStaff
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CategoryStaff $categoryStaff)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $categoryStaff);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CategoryStaff  $categoryStaff
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoryStaff $categoryStaff)
    {
        return $this->model->delete($categoryStaff);
    }
}
