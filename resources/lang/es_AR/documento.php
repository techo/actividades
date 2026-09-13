<?php

return [
    'invalido' => 'Ingresá un documento válido (:tipos).',
    'o'        => 'o',
    'tipo'     => [
        'dni'       => 'DNI',
        'cpf'       => 'CPF',
        'rut'       => 'RUT',
        'cc'        => 'cédula',
        'curp'      => 'CURP',
        'ci'        => 'cédula',
        'dpi'       => 'DPI',
        'dui'       => 'DUI',
        'identidad' => 'identidad',
        'documento' => 'documento',
        'pasaporte' => 'pasaporte',
    ],

    // Nombre del documento tal como se lo llama en cada país (label del campo en
    // los formularios). Clave = abreviación de atl_pais. Fuente única compartida
    // por registro, perfil y suscribe (vía DocumentoService::etiquetaCampo).
    'campo_generico' => 'Documento / Pasaporte',
    'campo_por_pais' => [
        'argentina'           => 'Número de DNI',
        'bolivia'             => 'Cédula de Identidad',
        'brasil'              => 'CPF',
        'chile'               => 'RUT',
        'colombia'            => 'Cédula de Ciudadanía',
        'costarica'           => 'Cédula de Identidad',
        'ecuador'             => 'Cédula de Ciudadanía',
        'elsalvador'          => 'DUI',
        'estadosunidos'       => 'Documento de Identidad',
        'guatemala'           => 'Documento Personal de Identificación',
        'honduras'            => 'DNI',
        'latam'               => 'Pasaporte',
        'mexico'              => 'INE / CURP',
        'panama'              => 'Cédula de Identidad',
        'paraguay'            => 'Cédula de Identidad',
        'peru'                => 'Documento de Identidad',
        'republicadominicana' => 'Cédula de Identidad',
        'uruguay'             => 'Cédula de Identidad',
        'venezuela'           => 'Cédula de Identidad',
    ],
];
