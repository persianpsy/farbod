<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBlogRequest;
use App\Models\Blog;
use App\Repositories\Repository;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;

class BlogController extends BaseController
{
    protected $model;
    public function __construct(Blog $blog)
    {
        $this->model =  new Repository($blog);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Blog $blog = null)
    {
        $conditions = array();
        if ($blog)
            $conditions['id'] = $blog->id;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateBlogRequest $request)
    {
        $blog = new Blog();

        if ($request->head) {
            $blog->head      = $request->head;
        }
        if ($request->body) {
            $blog->body      = $request->body;
        }
        if ($request->date) {
            $blog->date      = $request->date;
        }
        if ($request->user()) {
            $blog->user_id       = $request->user()->id;
        }
        
          if ($request->eng) {
            $blog->eng      = $request->eng;
        }

        if ($request->has('image')) {
            $destination_path ='/blog/image';
            $image_name = Verta::now()->format('Hmjs').rand(0,1000000);
            $path = $request->image->storeAs($destination_path,$image_name,"public");
            $blog->image     = env('APP_URL').'/public/storage/'.$path;
        }



        if($blog->save()) {

          return   $this->handleResponse(['data' => $blog],'created blog!');
        }
        return $this->handleResponse([],'error for created blog!');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function show(Blog $blog)
    {
        return $this->model->show($blog);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Blog $blog)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $blog);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Blog $blog)
    {
        return $this->model->delete($blog);
    }
}
