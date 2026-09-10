<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'nombre' => fake()->randomElement([
            'Programación',
            'Bases de Datos',
            'Redes',
            'Matemática',
            'Desarrollo Web',
        ]),

        'codigo' => fake()->unique()->bothify('??-####'),

        'semestre' => fake()->randomElement([
            'I-2026',
            'II-2026'
        ]),

        'creditos' => fake()->numberBetween(2, 5),

        'usuario_id' => Usuario::factory(),
    ];
}
}
