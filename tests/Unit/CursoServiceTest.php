<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Curso;
use App\Models\Pendiente;
use App\Models\Usuario;
use App\Services\CursoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Gate;

class CursoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permite_eliminar_curso_con_pendientes_activos(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'profesor',
        ]);

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

        $service->eliminar(
            $usuario,
            $curso
        );
    }

    public function test_permite_crear_curso_para_usuario_autorizado(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $datos = [
            'usuario_id' => $usuario->id,
            'nombre' => 'Redes',
            'codigo' => 'IF-5000',
            'creditos' => 4,
            'semestre' => 'II-2026',
        ];

        $service = new CursoService();

        $curso = $service->crear(
            $usuario,
            $datos
        );

        $this->assertDatabaseHas('cursos', [
            'id' => $curso->id,
            'usuario_id' => $usuario->id,
            'nombre' => 'Redes',
            'creditos' => 4,
        ]);
    }

    public function test_no_permite_acceder_a_curso_de_otro_usuario_desde_service(): void
    {
        $usuario1 = Usuario::factory()->create([
            'rol' => 'profesor',
        ]);

        $usuario2 = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $curso = Curso::factory()->create([
            'usuario_id' => $usuario2->id,
        ]);

        $this->expectException(
            AuthorizationException::class
        );

        $service = new CursoService();

        $service->mostrar(
            $usuario1,
            $curso
        );
    }

    public function test_limita_por_pagina_a_maximo_50(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        Curso::factory()
            ->count(55)
            ->create([
                'usuario_id' => $usuario->id,
            ]);

        $service = new CursoService();

        $resultado = $service->listar(
            $usuario,
            [
                'por_pagina' => 100,
            ]
        );

        $this->assertEquals(
            50,
            $resultado->perPage()
        );

        $this->assertCount(
            50,
            $resultado->items()
        );
    }

    public function test_mostrar_retorna_curso_autorizado_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $curso = Mockery::mock(Curso::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('view', $curso);

        $service = new CursoService();

        $resultado = $service->mostrar(
            $usuario,
            $curso
        );

        $this->assertSame($curso, $resultado);
    }


    public function test_actualizar_curso_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);

        $curso = Mockery::mock(Curso::class);

        $cursoActualizado = Mockery::mock(Curso::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('update', $curso);

        $curso->shouldReceive('update')
            ->once()
            ->with([
                'nombre' => 'Curso actualizado',
            ]);

        $curso->shouldReceive('fresh')
            ->once()
            ->andReturn($cursoActualizado);

        $service = new CursoService();

        $resultado = $service->actualizar(
            $usuario,
            $curso,
            [
                'nombre' => 'Curso actualizado',
            ]
        );

        $this->assertSame(
            $cursoActualizado,
            $resultado
        );
    }


    public function test_actualizar_no_permite_cambiar_usuario_id_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $curso = Mockery::mock(Curso::class);
        $cursoActualizado = Mockery::mock(Curso::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('update', $curso);

        // usuario_id debe ser eliminado antes de llamar update().
        $curso->shouldReceive('update')
            ->once()
            ->with([
                'nombre' => 'Nuevo nombre',
            ]);

        $curso->shouldReceive('fresh')
            ->once()
            ->andReturn($cursoActualizado);

        $service = new CursoService();

        $resultado = $service->actualizar(
            $usuario,
            $curso,
            [
                'usuario_id' => 999,
                'nombre' => 'Nuevo nombre',
            ]
        );

        $this->assertSame(
            $cursoActualizado,
            $resultado
        );
    }


    public function test_eliminar_curso_sin_pendientes_activos_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $curso = Mockery::mock(Curso::class);

        $gate = Mockery::mock();
        $relacion = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('delete', $curso);

        $curso->shouldReceive('pendientes')
            ->once()
            ->andReturn($relacion);

        $relacion->shouldReceive('whereIn')
            ->once()
            ->with(
                'estado',
                ['pendiente', 'en_progreso']
            )
            ->andReturnSelf();

        $relacion->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $curso->shouldReceive('delete')
            ->once();

        $service = new CursoService();

        $service->eliminar(
            $usuario,
            $curso
        );

        $this->assertTrue(true);
    }
}