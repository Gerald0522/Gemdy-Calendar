<?php

namespace Tests\Feature;

use App\Models\Pendiente;
use App\Models\Recordatorio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecordatorioApiTest extends TestCase
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

    public function test_usuario_puede_listar_recordatorios_de_pendiente_propio(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'fecha_limite' => now()->addDays(10)->toDateString(),
        ]);

        Recordatorio::factory()->count(2)->create([
            'usuario_id' => $usuario->id,
            'pendiente_id' => $pendiente->id,
        ]);

        $response = $this->getJson(
            "/api/pendientes/{$pendiente->id}/recordatorios"
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);

        $this->assertCount(
            2,
            $response->json('data')
        );
    }

    public function test_usuario_puede_consultar_recordatorio_de_pendiente_propio(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'fecha_limite' => now()->addDays(10)->toDateString(),
        ]);

        $recordatorio = Recordatorio::factory()->create([
            'usuario_id' => $usuario->id,
            'pendiente_id' => $pendiente->id,
        ]);

        $response = $this->getJson(
            "/api/pendientes/{$pendiente->id}/recordatorios/{$recordatorio->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.id',
                $recordatorio->id
            );
    }

    public function test_usuario_puede_crear_recordatorio_en_pendiente_propio(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'fecha_limite' => now()->addDays(10)->toDateString(),
        ]);

        $fechaRecordatorio = now()
            ->addDays(5)
            ->toDateString();

        $response = $this->postJson(
            "/api/pendientes/{$pendiente->id}/recordatorios",
            [
                'usuario_id' => $usuario->id,
                'titulo' => 'Recordar entrega',
                'descripcion' => 'Revisar el proyecto antes de entregar.',
                'fecha_recordatorio' => $fechaRecordatorio,
                'hora_inicio' => '18:00:00',
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.titulo',
                'Recordar entrega'
            )
            ->assertHeader('Location');

        $this->assertDatabaseHas('recordatorios', [
            'usuario_id' => $usuario->id,
            'pendiente_id' => $pendiente->id,
            'titulo' => 'Recordar entrega',
        ]);
    }

    public function test_crear_recordatorio_valida_campos_obligatorios(): void
    {
        $usuario = $this->autenticarUsuario();

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
            'fecha_limite' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->postJson(
            "/api/pendientes/{$pendiente->id}/recordatorios",
            []
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'usuario_id',
                'titulo',
                'fecha_recordatorio',
                'hora_inicio',
            ]);
    }
}