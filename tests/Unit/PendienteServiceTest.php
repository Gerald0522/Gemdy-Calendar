<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Curso;
use App\Models\Usuario;
use App\Services\PendienteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendienteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permite_asignar_curso_de_otro_usuario(): void
    {
        $usuario1 = Usuario::factory()->create();
        $usuario2 = Usuario::factory()->create();

        $curso = Curso::factory()->create([
            'usuario_id' => $usuario2->id,
        ]);

        $datos = [
            'usuario_id' => $usuario1->id,
            'curso_id' => $curso->id,
            'titulo' => 'Pendiente de prueba',
            'descripcion' => 'Prueba de regla de negocio',
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
            'hora_pendiente' => '18:00:00',
        ];

        $this->expectException(
            BusinessRuleException::class
        );

        $service = new PendienteService();

        $service->crear($datos);
    }

    public function test_no_permite_fecha_limite_anterior_a_hoy(): void
    {
        $usuario = Usuario::factory()->create();

        $datos = [
            'usuario_id' => $usuario->id,
            'curso_id' => null,
            'titulo' => 'Pendiente con fecha inválida',
            'descripcion' => 'Prueba de fecha límite',
            'estado' => 'pendiente',
            'fecha_limite' => now()->subDay()->format('Y-m-d'),
            'hora_pendiente' => '18:00:00',
        ];

        $this->expectException(
            BusinessRuleException::class
        );

        $service = new PendienteService();

        $service->crear($datos);
    }

    public function test_no_permite_recordatorio_despues_de_fecha_limite(): void
    {
        $usuario = Usuario::factory()->create();

        $datos = [
            'usuario_id' => $usuario->id,
            'curso_id' => null,
            'titulo' => 'Pendiente con recordatorio inválido',
            'descripcion' => 'Prueba de regla de negocio',
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
            'hora_pendiente' => '18:00:00',

            'recordatorio' => [
                'titulo' => 'Recordatorio inválido',
                'descripcion' => 'Está después de la fecha límite',
                'fecha_recordatorio' => now()->addDays(10)->format('Y-m-d'),
                'hora_inicio' => '10:00:00',
            ],
        ];

        $this->expectException(
            BusinessRuleException::class
        );

        $service = new PendienteService();

        $service->crearConRecordatorio($datos);
    }

}