<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\Province;
use App\Repositories\Repository;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    protected $model;
    public function __construct(County $county)
    {
        $this->model =  new Repository($county);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Province $province,County $county = null)
    {
        $conditions = array();
        if ($province)
            $conditions['province_id'] = $province->id;

        if ($county)
            $conditions['id'] = $county->id;

        if ($request->conditions)
            $conditions = json_decode($request->conditions,true);

        $model = $this->model;
        if ($conditions)
            $model = $this->model->list($conditions);

        if ($request->with)
            $model = $model->with($request->with);

        if ($conditions || $request->with)
            return $model->paginate(100);
        return $this->model->paginate(100);
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
     * @param  \App\Models\County  $county
     * @return \Illuminate\Http\Response
     */
    public function show(County $county)
    {
        return $this->model->show($county);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\County  $county
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, County $county)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $county);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\County  $county
     * @return \Illuminate\Http\Response
     */
    public function destroy(County $county)
    {
        return $this->model->delete($county);
    }
}
