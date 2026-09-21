<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecordatorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fecha_recordatorio' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i:s'],
        ];
    }

    public function messages(): array
    {
        return [
            'usuario_id.required' => 'El usuario es obligatorio.',
            'usuario_id.integer' => 'El usuario debe ser un identificador válido.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',

            'titulo.required' => 'El título del recordatorio es obligatorio.',
            'titulo.string' => 'El título del recordatorio debe ser texto.',
            'titulo.max' => 'El título del recordatorio no puede superar los 150 caracteres.',

            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',

            'fecha_recordatorio.required' => 'La fecha del recordatorio es obligatoria.',
            'fecha_recordatorio.date' => 'La fecha del recordatorio no es válida.',

            'hora_inicio.required' => 'La hora del recordatorio es obligatoria.',
            'hora_inicio.date_format' => 'La hora debe tener el formato HH:MM:SS.',
        ];
    }
}