<?php namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class Repository implements RepositoryInterface
{
    // model property on class instances
    protected $model;

    // Constructor to bind model to repo
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function role($string)
    {
        return $this->model->role($string);
    }

    private function uploadFile($file,$destination){
        $image_extention = $file->getClientOriginalExtension();
        $image_filename = rand(111111, 999999) . "." . $image_extention;
        $file->move($destination, $image_filename);
        return $image_filename;
    }

    // Get all instances of model
    public function all()
    {
        return $this->model->all();
    }

    public function list(array $data)
    {
        return $this->model->where($data);
    }

    public function whereIn($column, array $data)
    {
        return $this->model->whereIn($column,$data);
    }

    public function where(array $data)
    {
        return $this->model->where($data);
    }
    // Get all instances of model
    public function paginate($count = 15)
    {
        return $this->model->paginate($count);
    }
    public function whereLike($column, $value)
    {
        return $this->model->where($column,'LIKE',"%".$value."%");
    }
    //
    public function sortDec($column)
    {
        return $this->model->orderByDesc($column);
    }

    public function updateOrCreate(array $fields,array $data)
    {
        return $this->model->updateOrCreate($fields,$data);
    }
    // create a new record in the database
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    // update record in the database
    public function update(array $data, $model)
    {
        return $model->update($data);
    }

    // remove record from the database
    public function delete($model)
    {
        return $model->delete();
    }

    // show the record with the given id
    public function show($model)
    {
        return $model;
    }

    // show the record with the given id
    public function get($model)
    {
        return $model->get();
    }

    // sort items by date descending
    public function latest($model)
    {
        return $this->model->latest();
    }

    // Get the associated model
    public function getModel()
    {
        return $this->model;
    }

    // Set the associated model
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    // Eager load database relationships
    public function with($relations)
    {
        return $this->model->with($relations);
    }

    public function upload($file,$destination,$is_singular = true)
    {
        if ($is_singular) {
            return $this->upload($file,$destination);
        }else{
            $uploads = [];
            foreach ($file as $item){
                array_push($uploads,$this->upload($item,$destination));
            }
            return $uploads;
        }
    }

    public function deleteFile($name,$destination)
    {
        unlink($destination.'/'.$name);
    }
    public function whereHas($column, $func)
    {
        return $this->model->whereHas($column,$func);
    }
    public function orderBy($query,$order='asc')
    {
        if ($order==='asc'){
            return $this->model->orderBy($query);
        }else if ($order === 'desc') {
            return $this->model->orderByDesc($query);
        }
    }

}
