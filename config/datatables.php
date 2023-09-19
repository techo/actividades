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
            [
               'name' => 'pais',
                'sortField' => 'pais',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Pais',
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
            [
               'name' => 'pais',
                'sortField' => 'pais',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'Pais',
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
    ],
    'equipos' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idEquipo',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'Nombre'
            ],
            [
                'name' => 'area',
                'sortField' => 'area',
                'title' => 'Area'
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'Oficina'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'title' => 'Fecha de Inicio'
            ],
            [
                'name' => 'estado',
                'sortField' => 'estado',
                'title' => 'Estado'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'nombre',
                'sortField' => 'nombre',
                'direction' => 'asc'
            ],
        ]
    ],
    'integrantes' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idIntegrante',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'Nombre'
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'Rol'
            ],
            [
                'name' => 'despliegue',
                'sortField' => 'despliegue',
                'title' => 'Despliegue'
            ],

            [
                'name' => 'relacion',
                'sortField' => 'relacion',
                'title' => 'Relación'
            ],
            [
                'name' => 'estado',
                'sortField' => 'estado',
                'title' => 'Estado'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'title' => 'Fecha de Inicio'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'estado',
                'sortField' => 'estado',
                'direction' => 'asc'
            ],
        ]
    ],

    'provincias' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idProvincia',
                'visible' => false
            ],
            [
                'name' => 'provincia',
                'sortField' => 'provincia',
                'title' => 'Nombre'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'provincia',
                'sortField' => 'provincia',
                'direction' => 'asc'
            ],
        ]
    ],
    'localidades' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idLocalidad',
                'visible' => false
            ],
            [
                'name' => 'localidad',
                'sortField' => 'localidad',
                'title' => 'Nombre'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'localidad',
                'sortField' => 'localidad',
                'direction' => 'asc'
            ],
        ]
    ],
    'suscriptos' => [
        'fields' => [
            [
                'name' => 'idPersona',
                'sortField' => 'idPersona',
                'title' => 'Persona'
            ],
            [
                'name' => 'email',
                'sortField' => 'email',
                'title' => 'Mail'
            ],
            [
                'name' => 'perfil_seleccionado',
                'sortField' => 'perfil_seleccionado',
                'title' => 'Perfil'
            ],
            [
                'name' => 'tematica',
                'sortField' => 'tematica',
                'title' => 'Temática'
            ],
            [
                'name' => 'tiempo_disponible',
                'sortField' => 'tiempo_disponible',
                'title' => 'Tiempo Disponible'
            ],
            [
                'name' => 'created_at',
                'sortField' => 'created_at',
                'title' => 'Fecha Creación'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'created_at',
                'sortField' => 'created_at',
                'direction' => 'asc'
            ],
        ]
    ],
    'puntos' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'id',
                'visible' => false
            ],
            [
                'name' => 'punto',
                'title' => 'Punto'
            ],
            [
                'name' => 'horario',
                'title' => 'Hora'
            ],
            [
                'name' => 'provincia',
                'title' => 'Provincia'
            ],
            [
                'name' => 'localidad',
                'title' => 'Localidad'
            ],
            [
                'name' => 'nombres',
                'title' => 'Nombre'
            ],
            [
                'name' => 'apellidoPaterno',
                'title' => 'Apellido'
            ],
            [
                'name' => 'estado',
                'title' => 'Estado',
                'callback' => 'estado',
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'punto',
                'sortField' => 'punto',
                'direction' => 'asc'
            ],
        ]
    ],
    'oficinas' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'id',
                'visible' => false
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'Nombre'
            ],
            [
                'name' => 'pais',
                'sortField' => 'pais',
                'title' => 'País'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'oficina',
                'sortField' => 'oficina',
                'direction' => 'asc'
            ],
        ]
    ],
    'tiposActividad' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'id',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'Nombre'
            ],
            [
                'name' => 'categoria',
                'sortField' => 'categoria',
                'title' => 'Categoría',
                'callback' => 'traducir'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'nombre',
                'sortField' => 'nombre',
                'direction' => 'asc'
            ],
        ]
    ]
];
