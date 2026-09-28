<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordatorioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador del recordatorio.
             * @example 1
             */
            'id' => $this->id,

            /**
             * Identificador del usuario propietario.
             * @example 1
             */
            'usuario_id' => $this->usuario_id,

            /**
             * Identificador del pendiente asociado.
             * @example 1
             */
            'pendiente_id' => $this->pendiente_id,

            /**
             * Título del recordatorio.
             * @example Recordar entrega del proyecto
             */
            'titulo' => $this->titulo,

            /**
             * Descripción del recordatorio.
             * @example Revisar el proyecto antes de realizar la entrega.
             */
            'descripcion' => $this->descripcion,

            /**
             * Fecha del recordatorio.
             * @example 2026-10-14
             */
            'fecha_recordatorio' => $this->fecha_recordatorio?->format('Y-m-d'),

            /**
             * Hora del recordatorio.
             * @example 18:00:00
             */
            'hora_inicio' => $this->hora_inicio,
        ];
    }
}