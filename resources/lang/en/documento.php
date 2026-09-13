<?php

return [
    'invalido' => 'Enter a valid document (:tipos).',
    'o'        => 'or',
    'tipo'     => [
        'dni'       => 'ID',
        'cpf'       => 'CPF',
        'rut'       => 'RUT',
        'cc'        => 'ID card',
        'curp'      => 'CURP',
        'ci'        => 'ID card',
        'dpi'       => 'DPI',
        'dui'       => 'DUI',
        'identidad' => 'ID',
        'documento' => 'document',
        'pasaporte' => 'passport',
    ],

    // Document name as it is called in each country (form field label). Key =
    // atl_pais abbreviation. Single source shared by registro, perfil and
    // suscribe (via DocumentoService::etiquetaCampo).
    'campo_generico' => 'Document / Passport',
    'campo_por_pais' => [
        'argentina'           => 'DNI',
        'bolivia'             => 'Identity Card',
        'brasil'              => 'CPF',
        'chile'               => 'RUT',
        'colombia'            => 'Citizenship ID',
        'costarica'           => 'Identity Card',
        'ecuador'             => 'Citizenship ID',
        'elsalvador'          => 'DUI',
        'estadosunidos'       => 'ID Document',
        'guatemala'           => 'Personal Identification Document',
        'honduras'            => 'National ID',
        'latam'               => 'Passport',
        'mexico'              => 'INE / CURP',
        'panama'              => 'Identity Card',
        'paraguay'            => 'Identity Card',
        'peru'                => 'Identity Document',
        'republicadominicana' => 'Identity Card',
        'uruguay'             => 'Identity Card',
        'venezuela'           => 'Identity Card',
    ],
];
