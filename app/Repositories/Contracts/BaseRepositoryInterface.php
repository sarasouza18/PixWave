<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function getAll();
    public function findById($id);
    public function create(array $data);
    public function update(Model $model, array $data);
    public function delete(Model $model);
}
