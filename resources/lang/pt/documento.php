<?php

return [
    'invalido' => 'Digite um documento válido (:tipos).',
    'o'        => 'ou',
    'tipo'     => [
        'dni'       => 'DNI',
        'cpf'       => 'CPF',
        'rut'       => 'RUT',
        'cc'        => 'cédula',
        'curp'      => 'CURP',
        'ci'        => 'cédula',
        'dpi'       => 'DPI',
        'dui'       => 'DUI',
        'identidad' => 'identidade',
        'documento' => 'documento',
        'pasaporte' => 'passaporte',
    ],

    // Nome do documento como é chamado em cada país (label do campo nos
    // formulários). Chave = abreviação de atl_pais. Fonte única compartilhada
    // por registro, perfil e suscribe (via DocumentoService::etiquetaCampo).
    'campo_generico' => 'Documento / Passaporte',
    'campo_por_pais' => [
        'argentina'           => 'DNI',
        'bolivia'             => 'Carteira de Identidade',
        'brasil'              => 'CPF',
        'chile'               => 'RUT',
        'colombia'            => 'Cédula de Cidadania',
        'costarica'           => 'Carteira de Identidade',
        'ecuador'             => 'Cédula de Cidadania',
        'elsalvador'          => 'DUI',
        'estadosunidos'       => 'Documento de Identidade',
        'guatemala'           => 'Documento Pessoal de Identificação',
        'honduras'            => 'Documento de Identidade',
        'latam'               => 'Passaporte',
        'mexico'              => 'INE / CURP',
        'panama'              => 'Carteira de Identidade',
        'paraguay'            => 'Carteira de Identidade',
        'peru'                => 'Documento de Identidade',
        'republicadominicana' => 'Carteira de Identidade',
        'uruguay'             => 'Carteira de Identidade',
        'venezuela'           => 'Carteira de Identidade',
    ],
];
