<?php

namespace App\Repositories;

use App\Models\Gateway;
use App\Repositories\Contracts\GatewayRepositoryInterface;

class GatewayRepository extends BaseRepository implements GatewayRepositoryInterface
{
    public function getAvailableGateways()
    {
        return Gateway::where('available', true)->get();
    }

    public function findById($id)
    {
        return Gateway::find($id);
    }
}

