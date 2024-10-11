<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Returns all records
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Finds a record by ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Creates a new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Updates an existing record
     */
    public function update(Model $model, array $data)
    {
        return $model->update($data);
    }

    /**
     * Deletes a record
     */
    public function delete(Model $model)
    {
        return $model->delete();
    }
}
