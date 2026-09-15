<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Curso;

class CursoService
{
    public function listar(array $filtros)
    {
        $query = Curso::query();

        if (!empty($filtros['usuario_id'])) {
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

        $porPagina = min($porPagina, 50);

        return $query
            ->orderBy($orden, $direccion)
            ->paginate($porPagina);
    }

    public function crear(array $datos): Curso
    {
        return Curso::create($datos);
    }

    public function actualizar(
        Curso $curso,
        array $datos
    ): Curso {
        $curso->update($datos);

        return $curso->fresh();
    }

    public function eliminar(Curso $curso): void
    {
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