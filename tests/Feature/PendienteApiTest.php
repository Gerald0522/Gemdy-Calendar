<?php

namespace Tests\Feature;

use App\Models\Pendiente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PendienteApiTest extends TestCase
{
    use RefreshDatabase;

    private function autenticarUsuario(): Usuario
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        Sanctum::actingAs($usuario);

        return $usuario;
    }

    public function test_usuario_puede_listar_sus_pendientes(): void
    {
        $usuario = $this->autenticarUsuario();

        Pendiente::factory()->count(3)->create([
            'usuario_id' => $usuario->id,
        ]);

        $response = $this->getJson('/api/pendientes');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_usuario_puede_crear_pendiente(): void
    {
        $usuario = $this->autenticarUsuario();

        $response = $this->postJson('/api/pendientes', [
            'usuario_id' => $usuario->id,
            'curso_id' => null,
            'titulo' => 'Estudiar para examen',
            'descripcion' => 'Repasar los temas del curso.',
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(7)->toDateString(),
            'hora_pendiente' => '18:00:00',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.titulo',
                'Estudiar para examen'
            )
            ->assertHeader('Location');

        $this->assertDatabaseHas('pendientes', [
            'usuario_id' => $usuario->id,
            'titulo' => 'Estudiar para examen',
            'estado' => 'pendiente',
        ]);
    }

    public function test_crear_pendiente_valida_campos_obligatorios(): void
    {
        $this->autenticarUsuario();

        $response = $this->postJson('/api/pendientes', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'usuario_id',
                'titulo',
                'estado',
            ]);
    }

    public function test_usuario_puede_consultar_pendiente_propio(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        $response = $this->getJson(
            "/api/pendientes/{$pendiente->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.id',
                $pendiente->id
            );
    }

    public function test_usuario_puede_actualizar_pendiente_propio(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'titulo' => 'Título original',
            'estado' => 'pendiente',
        ]);

        $response = $this->putJson(
            "/api/pendientes/{$pendiente->id}",
            [
                'titulo' => 'Título actualizado',
                'estado' => 'en_progreso',
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.titulo',
                'Título actualizado'
            )
            ->assertJsonPath(
                'data.estado',
                'en_progreso'
            );

        $this->assertDatabaseHas('pendientes', [
            'id' => $pendiente->id,
            'titulo' => 'Título actualizado',
            'estado' => 'en_progreso',
        ]);
    }

    public function test_actualizar_pendiente_valida_estado_incorrecto(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        $response = $this->putJson(
            "/api/pendientes/{$pendiente->id}",
            [
                'estado' => 'estado_invalido',
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'estado',
            ]);
    }

    public function test_usuario_puede_eliminar_pendiente_propio(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        $this->deleteJson(
            "/api/pendientes/{$pendiente->id}"
        )->assertStatus(204);

        $this->assertDatabaseMissing('pendientes', [
            'id' => $pendiente->id,
        ]);
    }

    public function test_usuario_puede_crear_pendiente_con_recordatorio(): void
    {
        $usuario = $this->autenticarUsuario();

        $fechaRecordatorio = now()
            ->addDays(3)
            ->toDateString();

        $fechaLimite = now()
            ->addDays(7)
            ->toDateString();

        $response = $this->postJson(
            '/api/pendientes/con-recordatorio',
            [
                'usuario_id' => $usuario->id,
                'curso_id' => null,
                'titulo' => 'Entregar proyecto',
                'descripcion' => 'Proyecto de Desarrollo de Software',
                'estado' => 'pendiente',
                'fecha_limite' => $fechaLimite,
                'hora_pendiente' => '18:00:00',

                'recordatorio' => [
                    'titulo' => 'Recordar proyecto',
                    'descripcion' => 'Preparar entrega',
                    'fecha_recordatorio' => $fechaRecordatorio,
                    'hora_inicio' => '10:00:00',
                ],
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.titulo',
                'Entregar proyecto'
            )
            ->assertHeader('Location');

        $this->assertDatabaseHas('pendientes', [
            'usuario_id' => $usuario->id,
            'titulo' => 'Entregar proyecto',
        ]);

        $this->assertDatabaseHas('recordatorios', [
            'usuario_id' => $usuario->id,
            'titulo' => 'Recordar proyecto',
        ]);
    }

    public function test_crear_con_recordatorio_valida_recordatorio_obligatorio(): void
    {
        $usuario = $this->autenticarUsuario();

        $response = $this->postJson(
            '/api/pendientes/con-recordatorio',
            [
                'usuario_id' => $usuario->id,
                'titulo' => 'Pendiente sin recordatorio',
                'estado' => 'pendiente',
                'fecha_limite' => now()
                    ->addDays(7)
                    ->toDateString(),
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'recordatorio',
            ]);
    }

    public function test_usuario_puede_consultar_resumen_por_estado(): void
    {
        $usuario = $this->autenticarUsuario();

        Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'estado' => 'completado',
        ]);

        $response = $this->getJson(
            '/api/pendientes-estados'
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }
}