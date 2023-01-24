<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Repositories\Repository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $model;
    public function __construct(Category $category)
    {
        $this->model =  new Repository($category);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Category $category = null)
    {
        $conditions = array();
        if ($category)
            $conditions['id'] = $category->id;

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
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return $this->model->show($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        return $this->model->delete($category);
    }
}
