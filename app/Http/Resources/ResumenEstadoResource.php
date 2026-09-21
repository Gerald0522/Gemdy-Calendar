<?php

namespace App\Http\Resources;

use App\Models\Pendiente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Pendiente $resource
 */
class ResumenEstadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'estado' => $this->estado,
            'cantidad' => (int) $this->cantidad,
        ];
    }
}