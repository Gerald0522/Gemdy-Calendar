<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador del pendiente.
             * @example 1
             */
            'id' => $this->id,

            /**
             * Identificador del usuario propietario.
             * @example 1
             */
            'usuario_id' => $this->usuario_id,

            /**
             * Identificador del curso asociado.
             * @example 1
             */
            'curso_id' => $this->curso_id,

            /**
             * Título del pendiente.
             * @example Entregar proyecto de Desarrollo de Software
             */
            'titulo' => $this->titulo,

            /**
             * Descripción del pendiente.
             * @example Completar y entregar el proyecto final del curso.
             */
            'descripcion' => $this->descripcion,

            /**
             * Estado actual del pendiente.
             * @example pendiente
             */
            'estado' => $this->estado,

            /**
             * Fecha límite.
             * @example 2026-10-15
             */
            'fecha_limite' => $this->fecha_limite?->format('Y-m-d'),

            /**
             * Hora asociada al pendiente.
             * @example 18:00:00
             */
            'hora_pendiente' => $this->hora_pendiente,
        ];
    }
}