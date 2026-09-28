<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Curso;
use App\Models\Usuario;
use App\Services\PendienteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Gate;

class PendienteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permite_asignar_curso_de_otro_usuario(): void
    {
        $usuario1 = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $usuario2 = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

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

        $service->crear(
            $usuario1,
            $datos
        );
    }

    public function test_no_permite_fecha_limite_anterior_a_hoy(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

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

        $service->crear(
            $usuario,
            $datos
        );
    }

    public function test_no_permite_recordatorio_despues_de_fecha_limite(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

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
                'fecha_recordatorio' =>
                    now()->addDays(10)->format('Y-m-d'),
                'hora_inicio' => '10:00:00',
            ],
        ];

        $this->expectException(
            BusinessRuleException::class
        );

        $service = new PendienteService();

        $service->crearConRecordatorio(
            $usuario,
            $datos
        );
    }

    public function test_permite_crear_pendiente_correctamente(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $datos = [
            'usuario_id' => $usuario->id,
            'curso_id' => null,
            'titulo' => 'Estudiar redes',
            'descripcion' => 'Repasar para el examen',
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
            'hora_pendiente' => '18:00:00',
        ];

        $service = new PendienteService();

        $pendiente = $service->crear(
            $usuario,
            $datos
        );

        $this->assertDatabaseHas('pendientes', [
            'id' => $pendiente->id,
            'usuario_id' => $usuario->id,
            'titulo' => 'Estudiar redes',
            'estado' => 'pendiente',
        ]);
    }


    public function test_no_permite_acceder_a_pendiente_de_otro_usuario_desde_service(): void
    {
        $usuario1 = Usuario::factory()->create([
            'rol' => 'profesor',
        ]);

        $usuario2 = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $pendiente = \App\Models\Pendiente::factory()->create([
            'usuario_id' => $usuario2->id,
        ]);

        $this->expectException(
            \Illuminate\Auth\Access\AuthorizationException::class
        );

        $service = new PendienteService();

        $service->mostrar(
            $usuario1,
            $pendiente
        );
    }


    public function test_limita_por_pagina_de_pendientes_a_maximo_50(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        \App\Models\Pendiente::factory()
            ->count(55)
            ->create([
                'usuario_id' => $usuario->id,
            ]);

        $service = new PendienteService();

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

    public function test_permite_actualizar_pendiente_propio(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $pendiente = \App\Models\Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'titulo' => 'Título anterior',
            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $service = new PendienteService();

        $actualizado = $service->actualizar(
            $usuario,
            $pendiente,
            [
                'titulo' => 'Título actualizado',
                'fecha_limite' => now()->addDays(10)->format('Y-m-d'),
            ]
        );

        $this->assertEquals(
            'Título actualizado',
            $actualizado->titulo
        );

        $this->assertDatabaseHas('pendientes', [
            'id' => $pendiente->id,
            'titulo' => 'Título actualizado',
        ]);
    }


    public function test_permite_eliminar_pendiente_propio(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $pendiente = \App\Models\Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        $service = new PendienteService();

        $service->eliminar(
            $usuario,
            $pendiente
        );

        $this->assertDatabaseMissing('pendientes', [
            'id' => $pendiente->id,
        ]);
    }


    public function test_crea_pendiente_con_recordatorio_correctamente(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $datos = [
            'usuario_id' => $usuario->id,
            'curso_id' => null,
            'titulo' => 'Proyecto de redes',
            'descripcion' => 'Terminar proyecto',
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(10)->format('Y-m-d'),
            'hora_pendiente' => '18:00:00',

            'recordatorio' => [
                'titulo' => 'Recordar proyecto',
                'descripcion' => 'Avanzar el proyecto',
                'fecha_recordatorio' =>
                    now()->addDays(5)->format('Y-m-d'),
                'hora_inicio' => '17:00:00',
            ],
        ];

        $service = new PendienteService();

        $pendiente = $service->crearConRecordatorio(
            $usuario,
            $datos
        );

        $this->assertDatabaseHas('pendientes', [
            'id' => $pendiente->id,
            'usuario_id' => $usuario->id,
            'titulo' => 'Proyecto de redes',
        ]);

        $this->assertDatabaseHas('recordatorios', [
            'pendiente_id' => $pendiente->id,
            'usuario_id' => $usuario->id,
            'titulo' => 'Recordar proyecto',
        ]);

        $this->assertCount(
            1,
            $pendiente->recordatorios
        );
    }


    public function test_resumen_por_estado_solo_incluye_pendientes_del_usuario(): void
    {
        $usuario1 = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $usuario2 = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        \App\Models\Pendiente::factory()->count(2)->create([
            'usuario_id' => $usuario1->id,
            'estado' => 'pendiente',
        ]);

        \App\Models\Pendiente::factory()->create([
            'usuario_id' => $usuario1->id,
            'estado' => 'completado',
        ]);

        // Este pendiente pertenece a otro usuario y no debe contarse.
        \App\Models\Pendiente::factory()->count(3)->create([
            'usuario_id' => $usuario2->id,
            'estado' => 'pendiente',
        ]);

        $service = new PendienteService();

        $resumen = $service->resumenPorEstado($usuario1);

        $pendientes = $resumen->firstWhere(
            'estado',
            'pendiente'
        );

        $completados = $resumen->firstWhere(
            'estado',
            'completado'
        );

        $this->assertEquals(2, $pendientes->cantidad);
        $this->assertEquals(1, $completados->cantidad);
    }


    public function test_listar_aplica_filtros_y_corrige_orden_invalido(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $curso = Curso::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        \App\Models\Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'curso_id' => $curso->id,
            'estado' => 'pendiente',
            'titulo' => 'Pendiente filtrado',
            'fecha_limite' => now()->addDays(3)->format('Y-m-d'),
        ]);

        \App\Models\Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'curso_id' => $curso->id,
            'estado' => 'completado',
            'titulo' => 'No debe aparecer',
            'fecha_limite' => now()->addDays(4)->format('Y-m-d'),
        ]);

        $service = new PendienteService();

        $resultado = $service->listar(
            $usuario,
            [
                'estado' => 'pendiente',
                'curso_id' => $curso->id,
                'orden' => 'campo_invalido',
                'direccion' => 'direccion_invalida',
            ]
        );

        $this->assertCount(1, $resultado->items());

        $this->assertEquals(
            'Pendiente filtrado',
            $resultado->items()[0]->titulo
        );
    }

    public function test_mostrar_pendiente_autorizado_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $pendiente = Mockery::mock(\App\Models\Pendiente::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('view', $pendiente);

        $service = new PendienteService();

        $resultado = $service->mostrar(
            $usuario,
            $pendiente
        );

        $this->assertSame(
            $pendiente,
            $resultado
        );
    }


    public function test_eliminar_pendiente_autorizado_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $pendiente = Mockery::mock(\App\Models\Pendiente::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('delete', $pendiente);

        $pendiente->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $service = new PendienteService();

        $service->eliminar(
            $usuario,
            $pendiente
        );

        $this->assertTrue(true);
    }


    public function test_actualizar_no_permite_cambiar_usuario_id_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $pendiente = Mockery::mock(\App\Models\Pendiente::class);

        $pendienteActualizado =
            Mockery::mock(\App\Models\Pendiente::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('update', $pendiente);

        /*
        * actualizar() llama a toArray() para construir
        * los datos completos que serán validados.
        */
        $pendiente->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'usuario_id' => 1,
                'curso_id' => null,
                'titulo' => 'Título anterior',
                'fecha_limite' => null,
            ]);

        /*
        * usuario_id NO debe llegar a update(),
        * porque el Service lo elimina con unset().
        */
        $pendiente->shouldReceive('update')
            ->once()
            ->with([
                'titulo' => 'Título nuevo',
            ])
            ->andReturn(true);

        $pendiente->shouldReceive('fresh')
            ->once()
            ->andReturn($pendienteActualizado);

        $service = new PendienteService();

        $resultado = $service->actualizar(
            $usuario,
            $pendiente,
            [
                'usuario_id' => 999,
                'titulo' => 'Título nuevo',
            ]
        );

        $this->assertSame(
            $pendienteActualizado,
            $resultado
        );
    }


    public function test_actualizar_pendiente_correctamente_usando_dobles(): void
    {
        $usuario = Mockery::mock(Usuario::class);
        $pendiente = Mockery::mock(\App\Models\Pendiente::class);

        $pendienteActualizado =
            Mockery::mock(\App\Models\Pendiente::class);

        $gate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($usuario)
            ->andReturn($gate);

        $gate->shouldReceive('authorize')
            ->once()
            ->with('update', $pendiente);

        $pendiente->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'usuario_id' => 1,
                'curso_id' => null,
                'titulo' => 'Estudiar redes',
                'estado' => 'pendiente',
                'fecha_limite' => null,
            ]);

        $pendiente->shouldReceive('update')
            ->once()
            ->with([
                'titulo' => 'Estudiar redes y subneteo',
            ])
            ->andReturn(true);

        $pendiente->shouldReceive('fresh')
            ->once()
            ->andReturn($pendienteActualizado);

        $service = new PendienteService();

        $resultado = $service->actualizar(
            $usuario,
            $pendiente,
            [
                'titulo' => 'Estudiar redes y subneteo',
            ]
        );

        $this->assertSame(
            $pendienteActualizado,
            $resultado
        );
    }
    
}