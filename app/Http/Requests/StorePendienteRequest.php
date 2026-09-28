<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Identificador del usuario propietario del pendiente.
             * @example 1
             */
            'usuario_id' => [
                'required',
                'integer',
                'exists:usuarios,id',
            ],

            /**
             * Identificador del curso asociado.
             * @example 1
             */
            'curso_id' => [
                'nullable',
                'integer',
                'exists:cursos,id',
            ],

            /**
             * Título del pendiente.
             * @example Entregar proyecto de Desarrollo de Software
             */
            'titulo' => [
                'required',
                'string',
                'max:150',
            ],

            /**
             * Descripción del pendiente.
             * @example Completar y entregar el proyecto final del curso.
             */
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /**
             * Estado actual del pendiente.
             * @example pendiente
             */
            'estado' => [
                'required',
                'string',
                'in:pendiente,en_progreso,completado',
            ],

            /**
             * Fecha límite del pendiente.
             * @example 2026-10-15
             */
            'fecha_limite' => [
                'nullable',
                'date',
            ],

            /**
             * Hora asociada al pendiente.
             * @example 18:00:00
             */
            'hora_pendiente' => [
                'nullable',
                'date_format:H:i:s',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'usuario_id.required' => 'El usuario es obligatorio.',
            'usuario_id.integer' => 'El usuario debe ser un identificador válido.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',

            'curso_id.integer' => 'El curso debe ser un identificador válido.',
            'curso_id.exists' => 'El curso seleccionado no existe.',

            'titulo.required' => 'El título es obligatorio.',
            'titulo.string' => 'El título debe ser texto.',
            'titulo.max' => 'El título no puede superar los 150 caracteres.',

            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',

            'estado.required' => 'El estado es obligatorio.',
            'estado.string' => 'El estado debe ser texto.',
            'estado.in' => 'El estado debe ser pendiente, en_progreso o completado.',

            'fecha_limite.date' => 'La fecha límite debe ser una fecha válida.',

            'hora_pendiente.date_format' => 'La hora debe tener el formato HH:MM:SS.',
        ];
    }
}