<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RecordatorioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),

            'descripcion' => fake()->optional()->sentence(),

            'fecha_recordatorio' => fake()->dateTimeBetween(
                'now',
                '+1 month'
            ),

            'hora_inicio' => fake()->time(),
        ];
    }
}
