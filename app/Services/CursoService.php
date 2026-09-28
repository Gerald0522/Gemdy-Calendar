<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Curso;
use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;

class CursoService
{
    public function listar(
        Usuario $usuario,
        array $filtros
    ) {
        Gate::forUser($usuario)
            ->authorize('viewAny', Curso::class);

        $query = Curso::query();

        // Un usuario normal solamente puede ver sus propios cursos.
        // El administrador puede consultar todos.
        if (!$usuario->esAdmin()) {
            $query->where('usuario_id', $usuario->id);
        } elseif (!empty($filtros['usuario_id'])) {
            $query->where(
                'usuario_id',
                $filtros['usuario_id']
            );
        }

        if (!empty($filtros['semestre'])) {
            $query->where(
                'semestre',
                $filtros['semestre']
            );
        }

        $orden = $filtros['orden'] ?? 'nombre';
        $direccion = $filtros['direccion'] ?? 'asc';

        $camposPermitidos = [
            'nombre',
            'codigo',
            'creditos',
            'created_at',
        ];

        if (!in_array($orden, $camposPermitidos)) {
            $orden = 'nombre';
        }

        if (!in_array($direccion, ['asc', 'desc'])) {
            $direccion = 'asc';
        }

        $porPagina = (int) ($filtros['por_pagina'] ?? 10);
        $porPagina = max(1, min($porPagina, 50));

        return $query
            ->orderBy($orden, $direccion)
            ->paginate($porPagina);
    }

    public function crear(
        Usuario $usuario,
        array $datos
    ): Curso {
        Gate::forUser($usuario)
            ->authorize('create', Curso::class);

        // El curso siempre pertenece al usuario autenticado.
        // No confiamos en un usuario_id enviado desde el cliente.
        if (!$usuario->esAdmin()) {
            $datos['usuario_id'] = $usuario->id;
        }

        return Curso::create($datos);
    }

    public function mostrar(
        Usuario $usuario,
        Curso $curso
    ): Curso {
        Gate::forUser($usuario)
            ->authorize('view', $curso);

        return $curso;
    }

    public function actualizar(
        Usuario $usuario,
        Curso $curso,
        array $datos
    ): Curso {
        Gate::forUser($usuario)
            ->authorize('update', $curso);

        // Evita cambiar el propietario mediante una actualización.
        unset($datos['usuario_id']);

        $curso->update($datos);

        return $curso->fresh();
    }

    public function eliminar(
        Usuario $usuario,
        Curso $curso
    ): void {
        Gate::forUser($usuario)
            ->authorize('delete', $curso);

        $this->validarPendientesActivos($curso);

        $curso->delete();
    }

    private function validarPendientesActivos(
        Curso $curso
    ): void {
        $tienePendientesActivos = $curso
            ->pendientes()
            ->whereIn('estado', [
                'pendiente',
                'en_progreso',
            ])
            ->exists();

        if ($tienePendientesActivos) {
            throw new BusinessRuleException(
                'No se puede eliminar el curso porque tiene pendientes activos.'
            );
        }
    }
}