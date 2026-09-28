<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Identificador del usuario propietario del curso.
             * @example 1
             */
            'usuario_id' => [
                'required',
                'integer',
                'exists:usuarios,id',
            ],

            /**
             * Nombre del curso.
             * @example Desarrollo de Software
             */
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],

            /**
             * Código del curso.
             * @example IF-4101
             */
            'codigo' => [
                'required',
                'string',
                'max:20',

                Rule::unique('cursos', 'codigo')
                    ->where(
                        fn ($query) =>
                        $query->where('usuario_id', $this->usuario_id)
                    ),
            ],

            /**
             * Semestre en el que se imparte el curso.
             * @example II-2026
             */
            'semestre' => [
                'required',
                'string',
                'max:20',
            ],

            /**
             * Cantidad de créditos del curso.
             * @example 4
             */
            'creditos' => [
                'required',
                'integer',
                'min:1',
                'max:20',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'usuario_id.required' => 'El usuario es obligatorio.',
            'usuario_id.integer' => 'El usuario debe ser un identificador válido.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',

            'nombre.required' => 'El nombre del curso es obligatorio.',
            'nombre.string' => 'El nombre del curso debe ser texto.',
            'nombre.max' => 'El nombre del curso no puede superar los 100 caracteres.',

            'codigo.required' => 'El código del curso es obligatorio.',
            'codigo.string' => 'El código debe ser texto.',
            'codigo.max' => 'El código no puede superar los 20 caracteres.',
            'codigo.unique' => 'Este usuario ya tiene un curso con ese código.',

            'semestre.required' => 'El semestre es obligatorio.',
            'semestre.string' => 'El semestre debe ser texto.',
            'semestre.max' => 'El semestre no puede superar los 20 caracteres.',

            'creditos.required' => 'La cantidad de créditos es obligatoria.',
            'creditos.integer' => 'Los créditos deben ser un número entero.',
            'creditos.min' => 'El curso debe tener al menos 1 crédito.',
            'creditos.max' => 'El curso no puede superar los 20 créditos.',
        ];
    }
}