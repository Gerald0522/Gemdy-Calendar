<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registrar usuario
     *
     * Registra una nueva persona usuaria y genera un token de acceso.
     */
    public function register(RegisterRequest $request)
    {
        $usuario = Usuario::create($request->validated());

        $expiraEn = now()->addHours(8);

        $token = $usuario->createToken(
            'gemdy-calendar',
            [
                'cursos:read',
                'cursos:write',
                'pendientes:read',
                'pendientes:write',
                'recordatorios:read',
                'recordatorios:write',
            ],
            $expiraEn
        );

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'usuario' => [
                'id' => $usuario->id,
                'nombre1' => $usuario->nombre1,
                'nombre2' => $usuario->nombre2,
                'apellido1' => $usuario->apellido1,
                'apellido2' => $usuario->apellido2,
                'correo' => $usuario->correo,
                'telefono' => $usuario->telefono,
            ],
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiraEn->toISOString(),
        ], 201);
    }

    /**
     * Iniciar sesión
     *
     * Verifica las credenciales y genera un token de acceso.
     */
    public function login(LoginRequest $request)
    {
        $usuario = Usuario::where(
            'correo',
            $request->correo
        )->first();

        if (
            ! $usuario ||
            ! Hash::check($request->contrasena, $usuario->contrasena)
        ) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas no son válidas.',
            ], 401);
        }

        $expiraEn = now()->addHours(8);

        $token = $usuario->createToken(
            'gemdy-calendar',
            [
                'cursos:read',
                'cursos:write',
                'pendientes:read',
                'pendientes:write',
                'recordatorios:read',
                'recordatorios:write',
            ],
            $expiraEn
        );

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'usuario' => [
                'id' => $usuario->id,
                'nombre1' => $usuario->nombre1,
                'nombre2' => $usuario->nombre2,
                'apellido1' => $usuario->apellido1,
                'apellido2' => $usuario->apellido2,
                'correo' => $usuario->correo,
                'telefono' => $usuario->telefono,
            ],
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiraEn->toISOString(),
        ]);
    }

    /**
     * Mostrar usuario autenticado
     */
    public function me(Request $request)
    {
        return response()->json([
            'usuario' => $request->user(),
        ]);
    }

    /**
     * Cerrar sesión
     *
     * Revoca el token utilizado en la petición actual.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}