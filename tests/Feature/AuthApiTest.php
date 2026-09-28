<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_registrarse_y_recibe_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nombre1' => 'Gerald',
            'nombre2' => null,
            'apellido1' => 'Gutierrez',
            'apellido2' => null,
            'correo' => 'gerald@example.com',
            'telefono' => '88888888',
            'contrasena' => 'Password2026!',
            'contrasena_confirmation' => 'Password2026!',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'usuario' => [
                    'id',
                    'nombre1',
                    'nombre2',
                    'apellido1',
                    'apellido2',
                    'correo',
                    'telefono',
                ],
                'token',
                'token_type',
                'expires_at',
            ]);

        $this->assertDatabaseHas('usuarios', [
            'correo' => 'gerald@example.com',
        ]);

        $this->assertNotEmpty(
            $response->json('token')
        );

        $this->assertEquals(
            'Bearer',
            $response->json('token_type')
        );
    }


    public function test_usuario_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        $usuario = Usuario::factory()->create([
            'correo' => 'login@example.com',
            'contrasena' => 'Password2026!',
            'rol' => 'estudiante',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'correo' => 'login@example.com',
            'contrasena' => 'Password2026!',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'usuario',
                'token',
                'token_type',
                'expires_at',
            ]);

        $this->assertNotEmpty(
            $response->json('token')
        );

        $this->assertEquals(
            $usuario->id,
            $response->json('usuario.id')
        );
    }


    public function test_login_devuelve_mismo_error_para_contrasena_incorrecta_y_usuario_inexistente(): void
    {
        Usuario::factory()->create([
            'correo' => 'existe@example.com',
            'contrasena' => 'Password2026!',
            'rol' => 'estudiante',
        ]);

        $respuestaContrasena = $this->postJson(
            '/api/auth/login',
            [
                'correo' => 'existe@example.com',
                'contrasena' => 'Incorrecta2026!',
            ]
        );

        $respuestaUsuario = $this->postJson(
            '/api/auth/login',
            [
                'correo' => 'noexiste@example.com',
                'contrasena' => 'Incorrecta2026!',
            ]
        );

        $respuestaContrasena->assertStatus(401);
        $respuestaUsuario->assertStatus(401);

        $this->assertEquals(
            $respuestaContrasena->json('message'),
            $respuestaUsuario->json('message')
        );

        $this->assertEquals(
            'Las credenciales proporcionadas no son válidas.',
            $respuestaContrasena->json('message')
        );
    }


    public function test_usuario_autenticado_puede_consultar_sus_datos(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $token = $usuario->createToken(
            'test-token'
        )->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson('/api/auth/me');

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'usuario.id',
                $usuario->id
            );
    }


    public function test_logout_revoca_el_token_actual(): void
    {
        $usuario = Usuario::factory()->create([
            'rol' => 'estudiante',
        ]);

        $token = $usuario->createToken(
            'test-token'
        )->plainTextToken;

        $this->assertDatabaseCount(
            'personal_access_tokens',
            1
        );

        $this
            ->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Sesión cerrada correctamente.',
            ]);

        // El logout debe eliminar el token de Sanctum.
        $this->assertDatabaseCount(
            'personal_access_tokens',
            0
        );
    }

    public function test_login_limita_intentos_excesivos(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/auth/login', [
                'correo' => 'noexiste@example.com',
                'contrasena' => 'Incorrecta2026!',
            ])->assertStatus(401);
        }

        $response = $this->postJson('/api/auth/login', [
            'correo' => 'noexiste@example.com',
            'contrasena' => 'Incorrecta2026!',
        ]);

        $response
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Demasiados intentos. Intente nuevamente más tarde.',
            ]);
    }
}