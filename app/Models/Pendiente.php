<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendiente extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'curso_id',
        'titulo',
        'descripcion',
        'estado',
        'fecha_limite',
        'hora_pendiente',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function etiquetas()
    {
        return $this->belongsToMany(
            Etiqueta::class,
            'pendiente_etiqueta'
        )
        ->withPivot([
            'prioridad',
            'fecha_asignacion'
        ])
        ->withTimestamps();
    }

    public function recordatorios()
    {
        return $this->hasMany(Recordatorio::class);
    }

    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeDelCurso($query, $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    

    public function scopeProximos($query)
    {
        return $query
            ->whereNotNull('fecha_limite')
            ->orderBy('fecha_limite');
    }

    
}
