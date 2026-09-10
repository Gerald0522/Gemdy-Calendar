<?php

namespace Database\Factories;

use App\Models\Pendiente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pendiente>
 */
class PendienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(),

            'curso_id' => null,

            'titulo' => fake()->sentence(4),

            'descripcion' => fake()->sentence(),

            'estado' => fake()->randomElement([
                'pendiente',
                'en_progreso',
                'completado'
            ]),

            'fecha_limite' => fake()->dateTimeBetween(
                'now',
                '+2 months'
            ),

            'hora_pendiente' => fake()->time(),
        ];
    }
}
