<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Primer mes controlado por el sistema
    |--------------------------------------------------------------------------
    |
    | Queda sin definir hasta confirmar la fecha de
    | inicio de obligaciones de la cooperativa.
    |
    */

    'inicio_control' => env(
        'HOJAS_RUTA_INICIO_CONTROL',
        null
    ),

    /*
    |--------------------------------------------------------------------------
    | Habilitación de cobros
    |--------------------------------------------------------------------------
    |
    | Permanecerá deshabilitado hasta completar
    | las pruebas y los permisos de usuarios.
    |
    */

    'cobros_habilitados' => env(
        'HOJAS_RUTA_COBROS_HABILITADOS',
        false
    ),

];