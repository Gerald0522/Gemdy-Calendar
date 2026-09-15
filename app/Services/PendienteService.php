<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Exceptions\BusinessRuleException;
use App\Models\Curso;
use App\Models\Pendiente;

class PendienteService
{
    public function listar(array $filtros)
    {
        $query = Pendiente::query();

        if (!empty($filtros['estado'])) {
            $query->estado($filtros['estado']);
        }

        if (!empty($filtros['curso_id'])) {
            $query->delCurso($filtros['curso_id']);
        }

        $orden = $filtros['orden'] ?? 'fecha_limite';
        $direccion = $filtros['direccion'] ?? 'asc';

        $camposPermitidos = [
            'fecha_limite',
            'titulo',
            'estado',
            'created_at',
        ];

        if (!in_array($orden, $camposPermitidos)) {
            $orden = 'fecha_limite';
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

    public function crear(array $datos): Pendiente
    {
        $this->validarCursoDelUsuario($datos);
        $this->validarFechaLimite($datos);

        return Pendiente::create($datos);
    }

    public function actualizar(
        Pendiente $pendiente,
        array $datos
    ): Pendiente {
        $datosCompletos = array_merge(
            $pendiente->toArray(),
            $datos
        );

        $this->validarCursoDelUsuario($datosCompletos);
        $this->validarFechaLimite($datosCompletos);

        $pendiente->update($datos);

        return $pendiente->fresh();
    }

    public function eliminar(Pendiente $pendiente): void
    {
        $pendiente->delete();
    }

    private function validarCursoDelUsuario(array $datos): void
    {
        if (empty($datos['curso_id'])) {
            return;
        }

        $curso = Curso::find($datos['curso_id']);

        if (!$curso) {
            return;
        }

        if ($curso->usuario_id != $datos['usuario_id']) {
            throw new BusinessRuleException(
                'El curso seleccionado no pertenece al usuario indicado.'
            );
        }
    }

    private function validarFechaLimite(array $datos): void
    {
        if (empty($datos['fecha_limite'])) {
            return;
        }

        $fecha = strtotime($datos['fecha_limite']);

        if ($fecha < strtotime(date('Y-m-d'))) {
            throw new BusinessRuleException(
                'La fecha límite del pendiente no puede ser anterior a la fecha actual.'
            );
        }
    }

    public function crearConRecordatorio(array $datos): Pendiente
    {
        $datosRecordatorio = $datos['recordatorio'];

        unset($datos['recordatorio']);

        $this->validarCursoDelUsuario($datos);
        $this->validarFechaLimite($datos);

        return DB::transaction(function () use (
            $datos,
            $datosRecordatorio
        ) {
            $pendiente = Pendiente::create($datos);

            $this->validarFechaRecordatorio(
                $pendiente,
                $datosRecordatorio
            );

            $pendiente->recordatorios()->create([
                'usuario_id' => $pendiente->usuario_id,
                'titulo' => $datosRecordatorio['titulo'],
                'descripcion' =>
                    $datosRecordatorio['descripcion'] ?? null,
                'fecha_recordatorio' =>
                    $datosRecordatorio['fecha_recordatorio'],
                'hora_inicio' =>
                    $datosRecordatorio['hora_inicio'],
            ]);

            return $pendiente->load('recordatorios');
        });
    }

    private function validarFechaRecordatorio(
        Pendiente $pendiente,
        array $recordatorio
    ): void {
        $fechaRecordatorio = strtotime(
            $recordatorio['fecha_recordatorio']
        );

        $fechaLimite = strtotime(
            $pendiente->fecha_limite
        );

        if ($fechaRecordatorio > $fechaLimite) {
            throw new BusinessRuleException(
                'El recordatorio no puede programarse después de la fecha límite del pendiente.'
            );
        }
    }
}