<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Curso;
use App\Models\Pendiente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PendienteService
{
    public function listar(
        Usuario $usuario,
        array $filtros
    ) {
        Gate::forUser($usuario)
            ->authorize('viewAny', Pendiente::class);

        $query = Pendiente::query();

        // Los usuarios normales solamente ven sus pendientes.
        // El administrador puede consultar todos.
        if (!$usuario->esAdmin()) {
            $query->where('usuario_id', $usuario->id);
        } elseif (!empty($filtros['usuario_id'])) {
            $query->where(
                'usuario_id',
                $filtros['usuario_id']
            );
        }

        if (!empty($filtros['estado'])) {
            $query->estado($filtros['estado']);
        }

        if (!empty($filtros['curso_id'])) {
            $query->delCurso($filtros['curso_id']);
        }

        if (!empty($filtros['proximos'])) {
            $query->proximos();
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
        $porPagina = max(1, min($porPagina, 50));

        return $query
            ->orderBy($orden, $direccion)
            ->paginate($porPagina);
    }

    public function crear(
        Usuario $usuario,
        array $datos
    ): Pendiente {
        Gate::forUser($usuario)
            ->authorize('create', Pendiente::class);

        // El pendiente pertenece al usuario autenticado.
        if (!$usuario->esAdmin()) {
            $datos['usuario_id'] = $usuario->id;
        }

        $this->validarCursoDelUsuario($datos);
        $this->validarFechaLimite($datos);

        return Pendiente::create($datos);
    }

    public function mostrar(
        Usuario $usuario,
        Pendiente $pendiente
    ): Pendiente {
        Gate::forUser($usuario)
            ->authorize('view', $pendiente);

        return $pendiente;
    }

    public function actualizar(
        Usuario $usuario,
        Pendiente $pendiente,
        array $datos
    ): Pendiente {
        Gate::forUser($usuario)
            ->authorize('update', $pendiente);

        // Evita cambiar el propietario del pendiente.
        unset($datos['usuario_id']);

        $datosCompletos = array_merge(
            $pendiente->toArray(),
            $datos
        );

        $this->validarCursoDelUsuario($datosCompletos);
        $this->validarFechaLimite($datosCompletos);

        $pendiente->update($datos);

        return $pendiente->fresh();
    }

    public function eliminar(
        Usuario $usuario,
        Pendiente $pendiente
    ): void {
        Gate::forUser($usuario)
            ->authorize('delete', $pendiente);

        $pendiente->delete();
    }

    public function crearConRecordatorio(
        Usuario $usuario,
        array $datos
    ): Pendiente {
        Gate::forUser($usuario)
            ->authorize('create', Pendiente::class);

        if (!$usuario->esAdmin()) {
            $datos['usuario_id'] = $usuario->id;
        }

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

    public function resumenPorEstado(
        Usuario $usuario
    ) {
        Gate::forUser($usuario)
            ->authorize('viewAny', Pendiente::class);

        $query = Pendiente::query();

        if (!$usuario->esAdmin()) {
            $query->where('usuario_id', $usuario->id);
        }

        return $query
            ->selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->get();
    }

    private function validarCursoDelUsuario(
        array $datos
    ): void {
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

    private function validarFechaLimite(
        array $datos
    ): void {
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