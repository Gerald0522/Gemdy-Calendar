<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'curso_id' => $this->curso_id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'fecha_limite' => $this->fecha_limite?->format('Y-m-d'),
            'hora_pendiente' => $this->hora_pendiente,
        ];
    }
}