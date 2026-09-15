<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Curso;
use App\Models\Pendiente;
use App\Models\Usuario;
use App\Services\CursoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permite_eliminar_curso_con_pendientes_activos(): void
    {
        $usuario = Usuario::factory()->create();

        $curso = Curso::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'curso_id' => $curso->id,
            'estado' => 'pendiente',
        ]);

        $this->expectException(
            BusinessRuleException::class
        );

        $service = new CursoService();

        $service->eliminar($curso);
    }
}