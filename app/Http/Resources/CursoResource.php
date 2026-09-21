<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CursoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'semestre' => $this->semestre,
            'creditos' => $this->creditos,
        ];
    }
}