<?php

namespace App\Repositories\Contracts;

interface GatewayRepositoryInterface extends BaseRepositoryInterface
{
    public function getAvailableGateways();
}

