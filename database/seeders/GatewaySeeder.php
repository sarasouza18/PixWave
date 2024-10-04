<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gateway;

class GatewaySeeder extends Seeder
{
    public function run()
    {
        Gateway::create(['name' => 'Gateway A', 'available' => true]);
        Gateway::create(['name' => 'Gateway B', 'available' => true]);
    }
}

