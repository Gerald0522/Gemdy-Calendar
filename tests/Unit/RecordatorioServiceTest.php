<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Pendiente;
use App\Models\Usuario;
use App\Services\RecordatorioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Recordatorio;
use Illuminate\Support\Facades\Gate;
use Mockery;

class RecordatorioServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_permite_crear_recordatorio_correctamente(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'fecha_limite' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $datos = [
            'usuario_id' => $usuario->id,
            'titulo' => 'Estudiar para examen',
            'descripcion' => 'Repasar el contenido',
            'fecha_recordatorio' =>
                now()->addDays(5)->format('Y-m-d'),
            'hora_inicio' => '18:00:00',
        ];

        $service = new RecordatorioService();

        $recordatorio = $service->crear(
            $usuario,
            $pendiente,
            $datos
        );

        $this->assertDatabaseHas('recordatorios', [
            'id' => $recordatorio->id,
            'usuario_id' => $usuario->id,
            'pendiente_id' => $pendiente->id,
            'titulo' => 'Estudiar para examen',
        ]);
    }

    public function test_no_permite_recordatorio_despues_de_fecha_limite(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $datos = [
            'usuario_id' => $usuario->id,
            'titulo' => 'Recordatorio inválido',
            'descripcion' => 'Fecha posterior al pendiente',
            'fecha_recordatorio' =>
                now()->addDays(10)->format('Y-m-d'),
            'hora_inicio' => '18:00:00',
        ];

        $this->expectException(
            BusinessRuleException::class
        );

        $service = new RecordatorioService();

        $service->crear(
            $usuario,
            $pendiente,
            $datos
        );
    }

    public function test_no_permite_acceder_a_recordatorios_de_pendiente_ajeno_desde_service(): void
    {
        $usuario1 = Usuario::factory()->create([
            'rol' => 'profesor',
        ]);

        $usuario2 = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario2->id,
        ]);

        $this->expectException(
            AuthorizationException::class
        );

        $service = new RecordatorioService();

        $service->listarPorPendiente(
            $usuario1,
            $pendiente
        );
    }
    public function test_mostrar_recordatorio_correctamente_usando_dobles(): void
{
    $usuario = Mockery::mock(Usuario::class);
    $pendiente = Mockery::mock(Pendiente::class);
    $recordatorio = Mockery::mock(Recordatorio::class);

    $gate = Mockery::mock();

    Gate::shouldReceive('forUser')
        ->twice()
        ->with($usuario)
        ->andReturn($gate);

    $gate->shouldReceive('authorize')
        ->once()
        ->with('view', $pendiente);

    $gate->shouldReceive('authorize')
        ->once()
        ->with('view', $recordatorio);

    $pendiente->shouldReceive('getAttribute')
        ->with('id')
        ->andReturn(10);

    $recordatorio->shouldReceive('getAttribute')
        ->with('pendiente_id')
        ->andReturn(10);

    $service = new RecordatorioService();

    $resultado = $service->mostrar(
        $usuario,
        $pendiente,
        $recordatorio
    );

    $this->assertSame(
        $recordatorio,
        $resultado
    );
}


    public function test_mostrar_detecta_recordatorio_de_otro_pendiente_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $pendiente = Mockery::mock(Pendiente::class);
        $recordatorio = Mockery::mock(Recordatorio::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->twice()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('view', $pendiente);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('view', $recordatorio);

        $pendiente->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(10);

        $recordatorio->shouldReceive('getAttribute')
            ->with('pendiente_id')
            ->andReturn(20);

        $service = new RecordatorioService();

        $this->expectException(
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class
        );

        $service->mostrar(
            $usuario,
            $pendiente,
            $recordatorio
        );
    }


    public function test_listar_limita_por_pagina_a_50_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $pendiente = Mockery::mock(Pendiente::class);

        $gate = Mockery::mock();
        $relacion = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->twice()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('view', $pendiente);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('viewAny', Recordatorio::class);

        $pendiente->shouldReceive('recordatorios')
            ->once()
            ->andReturn($relacion);

        $relacion->shouldReceive('orderBy')
            ->once()
            ->with('fecha_recordatorio')
            ->andReturnSelf();

        $relacion->shouldReceive('orderBy')
            ->once()
            ->with('hora_inicio')
            ->andReturnSelf();

        $resultadoEsperado = Mockery::mock(
            \Illuminate\Contracts\Pagination\LengthAwarePaginator::class
        );

        $relacion->shouldReceive('paginate')
            ->once()
            ->with(50)
            ->andReturn($resultadoEsperado);

        $service = new RecordatorioService();

        $resultado = $service->listarPorPendiente(
            $usuario,
            $pendiente,
            [
                'por_pagina' => 100,
            ]
        );

        $this->assertSame(
            $resultadoEsperado,
            $resultado
        );
    }


    public function test_listar_corrige_paginacion_menor_a_uno_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $pendiente = Mockery::mock(Pendiente::class);

        $gate = Mockery::mock();
        $relacion = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->twice()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('view', $pendiente);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('viewAny', Recordatorio::class);

        $pendiente->shouldReceive('recordatorios')
            ->once()
            ->andReturn($relacion);

        $relacion->shouldReceive('orderBy')
            ->once()
            ->with('fecha_recordatorio')
            ->andReturnSelf();

        $relacion->shouldReceive('orderBy')
            ->once()
            ->with('hora_inicio')
            ->andReturnSelf();

        $resultadoEsperado = Mockery::mock(
            \Illuminate\Contracts\Pagination\LengthAwarePaginator::class
        );

        $relacion->shouldReceive('paginate')
            ->once()
            ->with(1)
            ->andReturn($resultadoEsperado);

        $service = new RecordatorioService();

        $resultado = $service->listarPorPendiente(
            $usuario,
            $pendiente,
            [
                'por_pagina' => -10,
            ]
        );

        $this->assertSame(
            $resultadoEsperado,
            $resultado
        );
    }
}