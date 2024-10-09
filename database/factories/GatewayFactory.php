<?php

namespace Database\Factories;

use App\Models\Gateway;
use Illuminate\Database\Eloquent\Factories\Factory;

class GatewayFactory extends Factory
{
    /**
     * O nome do modelo correspondente.
     *
     * @var string
     */
    protected $model = Gateway::class;

    /**
     * Define os valores padrões para o modelo Gateway.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Mercado Pago', 'Gerencianet']),
            'available' => $this->faker->boolean(),
            'api_key' => $this->faker->uuid,
        ];
    }
}
