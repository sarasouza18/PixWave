<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Retorna todos os registros
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Busca um registro por ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Cria um novo registro
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Atualiza um registro existente
     */
    public function update(Model $model, array $data)
    {
        return $model->update($data);
    }

    /**
     * Deleta um registro
     */
    public function delete(Model $model)
    {
        return $model->delete();
    }
}

