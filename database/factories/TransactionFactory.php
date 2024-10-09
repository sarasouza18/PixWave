<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Gateway;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\PaymentStatus;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'gateway_id' => Gateway::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'BRL',
            'status' => $this->faker->randomElement([
                PaymentStatus::PENDING->value,
                PaymentStatus::PAID->value,
                PaymentStatus::FAILED->value,
                PaymentStatus::PROCESSING->value
            ]),
            'gateway_status' => $this->faker->randomElement(['approved', 'pending', 'failed']),
        ];
    }
}

