<?php

return [
    'actividades' => [

        'fields' => [
            // {
            //   name: '__sequence',
            //   title: '#',
            //   titleClass: 'text-right',
            //   dataClass: 'text-right'
            // },
            // {
            //   name: '__checkbox',
            //   titleClass: 'text-center',
            //   dataClass: 'text-center',
            // },
            [
                'name' => 'nombreActividad',
                'title' => 'Nombre',
                'sortField' => 'nombreActividad',
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'Oficina'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Fecha Inicio',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'fechaFin',
                'sortField' => 'fechaFin',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Fecha Fin',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'tipoActividad',
                'sortField' => 'tipoActividad',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Tipo',
            ],
            [
                'name' => 'estadoConstruccion',
                'sortField' => 'estadoConstruccion',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Estado',
            ],
//            [
//                'name' => '__component:mis-actividades',
//                'title' => 'Acciones',
//                'titleClass' => 'text-center',
//                'dataClass' => 'text-center'
//            ]
        ],
        'sortOrder' => [
            [
                'sortField' => 'fechaCreacion',
                'direction' => 'desc'
            ]
        ],
    ],
    'voluntario-actividades' => [

        'fields' => [
            [
                'name' => 'idActividad',
                'sortField' => 'idActividad',
                'visible' => false
            ],
            [
                'name' => '__component:tarjeta-horizontal',
                'title' => 'Actividades Anteriores',
            ],
            [
                'name' => '__component:btn-mis-actividades',
                'title' => '',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center'
            ]
        ],
        'sortOrder' => [
            [
                'sortField' => 'fechaInicio',
                'direction' => 'desc'
            ]
        ],
    ],
    'mis-actividades' => [

        'fields' => [
            // {
            //   name: '__sequence',
            //   title: '#',
            //   titleClass: 'text-right',
            //   dataClass: 'text-right'
            // },
            // {
            //   name: '__checkbox',
            //   titleClass: 'text-center',
            //   dataClass: 'text-center',
            // },
            [
                'name' => 'nombreActividad',
                'title' => 'Nombre',
                'sortField' => 'nombreActividad',
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'Oficina'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Fecha Inicio',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'fechaFin',
                'sortField' => 'fechaFin',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Fecha Fin',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'tipoActividad',
                'sortField' => 'tipoActividad',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Tipo',
            ],
            [
                'name' => 'estadoConstruccion',
                'sortField' => 'estadoConstruccion',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Estado',
            ],
            // [
            //   name' => 'nickname',
            //   sortField' => 'nickname',
            //   callback' => 'allcap'
            // ],
            // [
//            [
//                'name' => '__component:mis-actividades',
//                'title' => 'Acciones',
//                'titleClass' => 'text-center',
//                'dataClass' => 'text-center'
//            ]
        ],
        'sortOrder' => [
            [
                'sortField' => 'fechaCreacion',
                'direction' => 'desc'
            ]
        ],
    ],
    'inscripciones' => [
        'fields' => [
            [
                'name' => '__checkbox',
                'titleClass' => 'center aligned',
                'dataClass' => 'center aligned'
            ],
            [
                'name' => 'id',
                'sortField' => 'idPersona',
                'visible' => false
            ],
            [
                'name' => 'dni',
                'sortField' => 'dni',
                'title' => 'DNI/Pasaporte',
            ],
            [
                'name' => 'nombres',
                'sortField' => 'nombres',
                'title' => 'Nombre'
            ],
            [
                'name' => 'apellidoPaterno',
                'sortField' => 'apellidoPaterno',
                'title' => 'Apellido'
            ],
//            [
//                'name' => 'mail',
//                'sortField' => 'mail',
//                'title' => 'Email'
//            ],
//            [
//                'name' => 'telefonoMovil',
//                'sortField' => 'telefonoMovil',
//                'title' => 'Teléfono Móvil'
//            ],
            [
                'name' => 'nombreGrupo',
                'sortField' => 'nombreGrupo',
                'title' => 'Grupo'
            ],
            [
                'name' => 'nombreRol',
                'sortField' => 'nombreRol',
                'title' => 'Rol'
            ],
            [
                'name' => '__component:asistencia',
                'title' => 'Asistencia',
                'titleClass' => 'text-center',
                'sortField' => 'presente',
                'dataClass' => 'text-center'
            ],
            [
                'name' => '__component:estado-inscripcion',
                'title' => 'Estado',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center'
            ],
//            [
//                'name' => '__component:actualizar-inscripcion',
//                'title' => 'Actualizar',
//                'titleClass' => 'text-center',
//                'dataClass' => 'text-center'
//            ],

        ],
        'sortOrder' => [
            [
                'field' => 'nombres',
                'sortField' => 'nombres',
                'direction' => 'asc'
            ]
        ],

    ],
    'miembros' => [
        'fields' => [
             [
               'name' => '__checkbox',
               'titleClass' => 'text-center',
               'dataClass' => 'text-center',
             ],
             [
               'name' => 'tipo',
               'callback' => 'getIcon'
             ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'Nombre',
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'Rol',
            ],
            [
                'name' => 'cantidad',
                'title' => 'Miembros',
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'nombre',
                'sortField' => 'nombre',
                'direction' => 'asc'
            ]
        ],

    ],
    'usuarios' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idPersona',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombres',
                'title' => 'Nombre'
            ],
            [
                'name' => 'apellido',
                'sortField' => 'apellidoPaterno',
                'title' => 'Apellido'
            ],
            [
                'name' => 'dni',
                'sortField' => 'dni',
                'title' => 'Documento'
            ],
            [
                'name' => 'email',
                'sortField' => 'mail',
                'title' => 'Email'
            ],
            [
                'name' => 'created_at',
                'sortField' => 'created_at',
                'title' => 'Fecha Creación'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'nombres',
                'sortField' => 'nombres',
                'direction' => 'asc'
            ],
        ]
    ]
];
