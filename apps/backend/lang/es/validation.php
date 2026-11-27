<?php

return [

    'array' => 'El campo :attribute debe ser una lista.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña actual no es correcta.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'enum' => 'El valor seleccionado en :attribute no es válido.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'in' => 'El valor seleccionado en :attribute no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'file' => 'El archivo :attribute no puede superar los :max kilobytes.',
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'mimes' => 'El archivo :attribute debe ser de tipo: :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'prohibited' => 'El campo :attribute no está permitido.',
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_with' => 'El campo :attribute es obligatorio cuando se envía :values.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El valor de :attribute ya está registrado.',

    'attributes' => [
        'login' => 'usuario o correo',
        'password' => 'contraseña',
        'current_password' => 'contraseña actual',
        'email' => 'correo electrónico',
        'token' => 'token',
        'first_name' => 'primer nombre',
        'middle_name' => 'segundo nombre',
        'first_surname' => 'primer apellido',
        'second_surname' => 'segundo apellido',
        'identification' => 'identificación',
        'birth_date' => 'fecha de nacimiento',
        'username' => 'usuario',
        'status' => 'estado',
        'per_page' => 'resultados por página',
        'filter.search' => 'búsqueda',
        'file' => 'archivo',
    ],

];
