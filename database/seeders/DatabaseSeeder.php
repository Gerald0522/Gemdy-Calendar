<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Curso;
use App\Models\Horario;
use App\Models\Pendiente;
use App\Models\Etiqueta;
use App\Models\Recordatorio;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        
        $usuarios = Usuario::factory(20)->create();

        
        foreach ($usuarios as $usuario) {

            
            $cursos = Curso::factory(3)->create([
                'usuario_id' => $usuario->id,
            ]);

            foreach ($cursos as $curso) {

                
                Horario::factory(2)->create([
                    'usuario_id' => $usuario->id,
                    'curso_id' => $curso->id,
                ]);

               
                Pendiente::factory(3)->create([
                    'usuario_id' => $usuario->id,
                    'curso_id' => $curso->id,
                ]);
            }
        }

      
        $etiquetas = Etiqueta::factory(5)->create();

        
        $pendientes = Pendiente::all();

      
        foreach ($pendientes as $pendiente) {
            $etiqueta = $etiquetas->random();

            $pendiente->etiquetas()->attach(
                $etiqueta->id,
                [
                    'prioridad' => fake()->randomElement([
                        'baja',
                        'media',
                        'alta',
                    ]),
                    'fecha_asignacion' => now()->toDateString(),
                ]
            );

            
            Recordatorio::factory()->create([
                'usuario_id' => $pendiente->usuario_id,
                'pendiente_id' => $pendiente->id,
            ]);
        }
    }
}
