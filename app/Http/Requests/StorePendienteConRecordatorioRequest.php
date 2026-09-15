<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendienteConRecordatorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'curso_id' => ['nullable', 'integer', 'exists:cursos,id'],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => [
                'required',
                'string',
                'in:pendiente,en_progreso,completado'
            ],
            'fecha_limite' => ['required', 'date'],
            'hora_pendiente' => ['nullable', 'date_format:H:i:s'],

            'recordatorio' => ['required', 'array'],
            'recordatorio.titulo' => [
                'required',
                'string',
                'max:150'
            ],
            'recordatorio.descripcion' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'recordatorio.fecha_recordatorio' => [
                'required',
                'date'
            ],
            'recordatorio.hora_inicio' => [
                'required',
                'date_format:H:i:s'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'usuario_id.required' =>
                'El usuario es obligatorio.',
            'usuario_id.exists' =>
                'El usuario seleccionado no existe.',

            'curso_id.exists' =>
                'El curso seleccionado no existe.',

            'titulo.required' =>
                'El título del pendiente es obligatorio.',
            'titulo.max' =>
                'El título no puede superar los 150 caracteres.',

            'estado.required' =>
                'El estado es obligatorio.',
            'estado.in' =>
                'El estado debe ser pendiente, en_progreso o completado.',

            'fecha_limite.required' =>
                'La fecha límite es obligatoria.',
            'fecha_limite.date' =>
                'La fecha límite debe ser válida.',

            'hora_pendiente.date_format' =>
                'La hora del pendiente debe tener el formato HH:MM:SS.',

            'recordatorio.required' =>
                'El recordatorio es obligatorio.',
            'recordatorio.array' =>
                'El recordatorio debe contener datos válidos.',

            'recordatorio.titulo.required' =>
                'El título del recordatorio es obligatorio.',

            'recordatorio.fecha_recordatorio.required' =>
                'La fecha del recordatorio es obligatoria.',
            'recordatorio.fecha_recordatorio.date' =>
                'La fecha del recordatorio debe ser válida.',

            'recordatorio.hora_inicio.required' =>
                'La hora del recordatorio es obligatoria.',
            'recordatorio.hora_inicio.date_format' =>
                'La hora del recordatorio debe tener el formato HH:MM:SS.',
        ];
    }
}