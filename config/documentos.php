<?php

/*
|--------------------------------------------------------------------------
| Reglas de validación de documento por país (Fase 1)
|--------------------------------------------------------------------------
|
| Fuente de verdad, data-driven, para validar el "documento" de una persona
| según su país. Evita hardcodear formatos en los controllers y permite sumar
| un país sin tocar código (solo esta config). Lo consume App\Services\Documento\
| DocumentoService a través de la regla App\Rules\DocumentoValido.
|
| - 'paises' mapea la ABREVIACIÓN de atl_pais (no el id, que es frágil) a la
|   lista de tipos aceptados, EN ORDEN DE PRIORIDAD: el documento nacional
|   primero y 'pasaporte' al final. El validador prueba en orden y usa el
|   primer tipo que matchea para normalizar (así un número que también podría
|   leerse como pasaporte se guarda como el documento nacional).
| - Un país sin entrada explícita usa 'default' (permisivo: no bloqueamos un
|   país que todavía no configuramos).
| - 'tipos' define cada tipo: cómo se normaliza, el regex sobre el valor YA
|   normalizado, y opcionalmente un algoritmo de dígito verificador ('check').
|
| Normalizadores (ver DocumentoService):
|   'digitos' -> deja solo [0-9]
|   'alnum'   -> mayúsculas + solo [A-Z0-9]
|   'rut'     -> alnum, y separa cuerpo + dígito verificador (K permitido)
|
| Checks implementados en Fase 1: 'cpf' (Brasil), 'rut' (Chile). El resto
| valida formato/largo. Sumar un check nuevo = implementarlo en DocumentoService
| y referenciarlo acá.
*/

return [

    'paises' => [
        'argentina' => ['dni_ar', 'pasaporte'],
        'brasil'    => ['cpf', 'pasaporte'],
        'chile'     => ['rut', 'pasaporte'],
        'colombia'  => ['cc_co', 'pasaporte'],
        'mexico'    => ['curp', 'pasaporte'],
        'peru'      => ['dni_pe', 'pasaporte'],
        'uruguay'   => ['ci_uy', 'pasaporte'],
        'paraguay'  => ['ci_py', 'pasaporte'],
        'bolivia'   => ['ci_bo', 'pasaporte'],
    ],

    // Para cualquier país no listado arriba: permisivo pero no basura.
    'default' => ['generico', 'pasaporte'],

    'tipos' => [
        // Argentina: DNI 7 u 8 dígitos. NO tiene dígito verificador.
        'dni_ar' => [
            'label'     => 'dni',
            'normaliza' => 'digitos',
            'regex'     => '/^\d{7,8}$/',
        ],

        // Brasil: CPF 11 dígitos con doble dígito verificador (mód. 11).
        'cpf' => [
            'label'     => 'cpf',
            'normaliza' => 'digitos',
            'regex'     => '/^\d{11}$/',
            'check'     => 'cpf',
        ],

        // Chile: RUT/RUN, cuerpo 7-8 dígitos + DV (0-9 o K), verificador mód. 11.
        'rut' => [
            'label'     => 'rut',
            'normaliza' => 'rut',
            'regex'     => '/^\d{7,8}[0-9K]$/',
            'check'     => 'rut',
        ],

        // Colombia: Cédula de Ciudadanía, numérica (sin verificador estándar).
        'cc_co' => [
            'label'     => 'cc',
            'normaliza' => 'digitos',
            'regex'     => '/^\d{6,10}$/',
        ],

        // México: CURP, 18 caracteres. En Fase 1 se valida el formato (el dígito
        // verificador queda como mejora posterior para no arriesgar falsos negativos).
        'curp' => [
            'label'     => 'curp',
            'normaliza' => 'alnum',
            'regex'     => '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$/',
        ],

        // Perú: DNI 8 dígitos.
        'dni_pe' => [
            'label'     => 'dni',
            'normaliza' => 'digitos',
            'regex'     => '/^\d{8}$/',
        ],

        // Uruguay: CI 7-8 dígitos (verificador como mejora posterior).
        'ci_uy' => [
            'label'     => 'ci',
            'normaliza' => 'digitos',
            'regex'     => '/^\d{7,8}$/',
        ],

        // Paraguay: CI numérica.
        'ci_py' => [
            'label'     => 'ci',
            'normaliza' => 'digitos',
            'regex'     => '/^\d{6,8}$/',
        ],

        // Bolivia: CI alfanumérica (varía por departamento).
        'ci_bo' => [
            'label'     => 'ci',
            'normaliza' => 'alnum',
            'regex'     => '/^[A-Z0-9]{5,12}$/',
        ],

        // Genérico (países sin regla específica): documento alfanumérico razonable.
        'generico' => [
            'label'     => 'documento',
            'normaliza' => 'alnum',
            'regex'     => '/^[A-Z0-9]{5,20}$/',
        ],

        // Pasaporte: alfanumérico, permisivo (varía por país emisor) PERO con al
        // menos una letra. Sin esto, un documento nacional numérico mal tipeado
        // (p. ej. un CPF que falla el verificador) pasaría "como pasaporte" y
        // anularía el check digit. La mayoría de los pasaportes llevan letras.
        'pasaporte' => [
            'label'     => 'pasaporte',
            'normaliza' => 'alnum',
            'regex'     => '/^(?=.*[A-Z])[A-Z0-9]{5,20}$/',
        ],
    ],

];
