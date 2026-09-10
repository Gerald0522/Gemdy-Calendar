<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HorarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dia_semana' => fake()->randomElement([
                'lunes',
                'martes',
                'miércoles',
                'jueves',
                'viernes',
            ]),

            'hora_inicio' => fake()->randomElement([
                '08:00:00',
                '10:00:00',
                '13:00:00',
                '15:00:00',
            ]),

            'hora_fin' => fake()->randomElement([
                '09:50:00',
                '11:50:00',
                '14:50:00',
                '16:50:00',
            ]),

            'salon' => fake()->optional()->bothify('A-##'),
        ];
    }
}
