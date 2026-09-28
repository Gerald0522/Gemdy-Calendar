<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Pendiente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_autenticado_no_puede_listar_cursos(): void
    {
        $response = $this->getJson('/api/cursos');

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'No autenticado.',
            ]);
    }

    public function test_estudiante_puede_listar_cursos(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/cursos');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_estudiante_no_puede_eliminar_curso_por_rol(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $curso = Curso::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->deleteJson(
            "/api/cursos/{$curso->id}"
        );

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' =>
                    'No tiene permisos para realizar esta acción.',
            ]);

        $this->assertDatabaseHas('cursos', [
            'id' => $curso->id,
        ]);
    }

    public function test_profesor_puede_consultar_curso_propio(): void
    {
        $profesor = Usuario::factory()->create([
            'rol' => 'profesor',
        ]);

        $curso = Curso::factory()->create([
            'usuario_id' => $profesor->id,
        ]);

        Sanctum::actingAs($profesor);

        $response = $this->getJson(
            "/api/cursos/{$curso->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.id',
                $curso->id
            );
    }

    public function test_usuario_no_puede_consultar_pendiente_ajeno(): void
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

        Sanctum::actingAs($usuario1);

        $response = $this->getJson(
            "/api/pendientes/{$pendiente->id}"
        );

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' =>
                    'No tiene permisos para acceder a este recurso.',
            ]);
    }

    public function test_usuario_puede_consultar_recordatorios_de_pendiente_propio(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $pendiente = Pendiente::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        Sanctum::actingAs($usuario);

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
    }
}