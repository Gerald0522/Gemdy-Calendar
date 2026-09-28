<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Pendiente;
use App\Models\Recordatorio;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class RecordatorioService
{
    public function listarPorPendiente(
        Usuario $usuario,
        Pendiente $pendiente,
        array $filtros = []
    ): LengthAwarePaginator {

        // El usuario debe tener permiso para acceder
        // al pendiente padre.
        Gate::forUser($usuario)
            ->authorize('view', $pendiente);

        Gate::forUser($usuario)
            ->authorize('viewAny', Recordatorio::class);

        $porPagina = (int) ($filtros['por_pagina'] ?? 10);
        $porPagina = max(1, min($porPagina, 50));

        return $pendiente->recordatorios()
            ->orderBy('fecha_recordatorio')
            ->orderBy('hora_inicio')
            ->paginate($porPagina);
    }

    public function mostrar(
        Usuario $usuario,
        Pendiente $pendiente,
        Recordatorio $recordatorio
    ): Recordatorio {
        Gate::forUser($usuario)
            ->authorize('view', $pendiente);

        Gate::forUser($usuario)
            ->authorize('view', $recordatorio);

        if (
            (int) $recordatorio->pendiente_id !==
            (int) $pendiente->id
        ) {
            abort(404);
        }

        return $recordatorio;
    }

    public function crear(
        Usuario $usuario,
        Pendiente $pendiente,
        array $datos
    ): Recordatorio {

        // Primero comprobamos que pueda acceder
        // al pendiente.
        Gate::forUser($usuario)
            ->authorize('view', $pendiente);

        Gate::forUser($usuario)
            ->authorize('create', Recordatorio::class);

        // El propietario no puede ser falsificado
        // desde el JSON enviado por el cliente.
        if (!$usuario->esAdmin()) {
            $datos['usuario_id'] = $usuario->id;
        }

        $this->validarUsuario($pendiente, $datos);
        $this->validarFecha($pendiente, $datos);

        $datos['pendiente_id'] = $pendiente->id;

        return Recordatorio::create($datos);
    }

    private function validarUsuario(
        Pendiente $pendiente,
        array $datos
    ): void {
        if (
            (int) $pendiente->usuario_id !==
            (int) $datos['usuario_id']
        ) {
            throw new BusinessRuleException(
                'El recordatorio debe pertenecer al mismo usuario del pendiente.'
            );
        }
    }

    private function validarFecha(
        Pendiente $pendiente,
        array $datos
    ): void {
        if (
            $pendiente->fecha_limite &&
            strtotime($datos['fecha_recordatorio']) >
            strtotime($pendiente->fecha_limite)
        ) {
            throw new BusinessRuleException(
                'La fecha del recordatorio no puede ser posterior a la fecha límite del pendiente.'
            );
        }
    }
}