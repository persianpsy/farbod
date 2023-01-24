<?php

namespace App\Repositories;

interface RepositoryInterface
{
    public function all();

    public function paginate();

    public function create(array $data);

    public function update(array $data, $model);

    public function delete($model);

    public function show($model);

    public function getModel();

    public function setModel($model);

    public function with($relations);

    public function upload($file, $destination, $is_singular = true);

    public function deleteFile($name, $destination);
}
