<?php

namespace App\Repositories\Contracts;

interface TransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUserId($userId);
}
