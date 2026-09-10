<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recordatorio extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'pendiente_id',
        'titulo',
        'descripcion',
        'fecha_recordatorio',
        'hora_inicio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_recordatorio' => 'date',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function pendiente()
    {
        return $this->belongsTo(Pendiente::class);
    }
}
