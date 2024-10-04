<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function getById($id)
    {
        return Transaction::find($id);
    }

    public function getByUserId($userId)
    {
        // TODO: Implement getByUserId() method.
    }
}

