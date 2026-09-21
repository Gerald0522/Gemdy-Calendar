<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordatorioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'pendiente_id' => $this->pendiente_id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'fecha_recordatorio' => $this->fecha_recordatorio?->format('Y-m-d'),
            'hora_inicio' => $this->hora_inicio,
        ];
    }
}