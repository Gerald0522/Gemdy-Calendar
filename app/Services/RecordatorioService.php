<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Pendiente;
use App\Models\Recordatorio;
use Illuminate\Database\Eloquent\Collection;

class RecordatorioService
{
    public function listarPorPendiente(Pendiente $pendiente): Collection
    {
        return $pendiente->recordatorios()
            ->orderBy('fecha_recordatorio')
            ->orderBy('hora_inicio')
            ->get();
    }

    public function crear(
        Pendiente $pendiente,
        array $datos
    ): Recordatorio {
        $this->validarUsuario($pendiente, $datos);
        $this->validarFecha($pendiente, $datos);

        $datos['pendiente_id'] = $pendiente->id;

        return Recordatorio::create($datos);
    }

    private function validarUsuario(
        Pendiente $pendiente,
        array $datos
    ): void {
        if ((int) $pendiente->usuario_id !== (int) $datos['usuario_id']) {
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