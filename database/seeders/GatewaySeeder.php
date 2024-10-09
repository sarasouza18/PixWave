<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gateway;

class GatewaySeeder extends Seeder
{
    public function run()
    {
        Gateway::create(['name' => 'Mercado Pago', 'available' => true]);
        Gateway::create(['name' => 'Gerencianet', 'available' => true]);
    }
}

