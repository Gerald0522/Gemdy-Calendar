<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CursoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador del curso.
             * @example 1
             */
            'id' => $this->id,

            /**
             * Identificador del usuario propietario.
             * @example 1
             */
            'usuario_id' => $this->usuario_id,

            /**
             * Nombre del curso.
             * @example Desarrollo de Software
             */
            'nombre' => $this->nombre,

            /**
             * Código del curso.
             * @example IF-4101
             */
            'codigo' => $this->codigo,

            /**
             * Semestre del curso.
             * @example II-2026
             */
            'semestre' => $this->semestre,

            /**
             * Cantidad de créditos.
             * @example 4
             */
            'creditos' => $this->creditos,
        ];
    }
}