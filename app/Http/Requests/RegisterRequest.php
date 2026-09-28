<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre1' => [
                'required',
                'string',
                'max:50',
            ],

            'nombre2' => [
                'nullable',
                'string',
                'max:50',
            ],

            'apellido1' => [
                'required',
                'string',
                'max:50',
            ],

            'apellido2' => [
                'nullable',
                'string',
                'max:50',
            ],

            'correo' => [
                'required',
                'email',
                'max:100',
                'unique:usuarios,correo',
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:20',
            ],

            'contrasena' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre1.required' => 'El primer nombre es obligatorio.',
            'nombre1.string' => 'El primer nombre debe ser texto.',
            'nombre1.max' => 'El primer nombre no puede superar los 50 caracteres.',

            'nombre2.string' => 'El segundo nombre debe ser texto.',
            'nombre2.max' => 'El segundo nombre no puede superar los 50 caracteres.',

            'apellido1.required' => 'El primer apellido es obligatorio.',
            'apellido1.string' => 'El primer apellido debe ser texto.',
            'apellido1.max' => 'El primer apellido no puede superar los 50 caracteres.',

            'apellido2.string' => 'El segundo apellido debe ser texto.',
            'apellido2.max' => 'El segundo apellido no puede superar los 50 caracteres.',

            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'correo.max' => 'El correo no puede superar los 100 caracteres.',
            'correo.unique' => 'El correo ya está registrado.',

            'telefono.string' => 'El teléfono debe ser texto.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',

            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}