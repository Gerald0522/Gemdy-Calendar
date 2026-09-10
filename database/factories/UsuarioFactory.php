<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'nombre1' => fake()->firstName(),
        'nombre2' => fake()->optional()->firstName(),

        'apellido1' => fake()->lastName(),
        'apellido2' => fake()->optional()->lastName(),

        'correo' => fake()->unique()->safeEmail(),

        'telefono' => fake()->numerify('########'),

        'contrasena' => bcrypt('12345678'),
    ];
}
}
