<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'nombre',
        'codigo',
        'semestre',
        'creditos',
    ];

    protected function casts(): array
    {
        return [
            'creditos' => 'integer',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function pendientes()
    {
        return $this->hasMany(Pendiente::class);
    }
}
