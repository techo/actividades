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
                'title' => 'backend.name',
                'sortField' => 'nombreActividad',
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'backend.office'
            ],
            [
                'name' => '__component:comunidades',
                'sortField' => 'comunidades',
                'title' => 'backend.community'
            ],
            [
                'name' => '__component:estadoActividad',
                'title' => 'backend.activity_status'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.start_dates',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'fechaFin',
                'sortField' => 'fechaFin',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.end_date',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'tipoActividad',
                'sortField' => 'tipoActividad',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.type',
            ],
            [
                'name' => 'estadoConstruccion',
                'sortField' => 'estadoConstruccion',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.state',
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
                'title' => 'backend.name',
                'sortField' => 'nombreActividad',
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'backend.office'
            ],
            [
                'name' => '__component:comunidades',
                'sortField' => 'comunidades',
                'title' => 'backend.community'
            ],
            [
                'name' => '__component:estadoActividad',
                'title' => 'backend.activity_status'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.start_date',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'fechaFin',
                'sortField' => 'fechaFin',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.end_date',
                'callback' => 'formatDate|DD-MM-YYYY'
            ],
            [
                'name' => 'tipoActividad',
                'sortField' => 'tipoActividad',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.type',
            ],
            [
                'name' => 'estadoConstruccion',
                'sortField' => 'estadoConstruccion',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center',
                'title' => 'backend.state',
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
                'name' => '__component:photoPerfil',
                'title' => '',
            ],
            [
                'name' => 'dni',
                'sortField' => 'dni',
                'title' => 'backend.dni',
                'visible' => false
            ],
            [
                'name' => 'nombres',
                'sortField' => 'nombres',
                'title' => 'backend.name'
            ],
            [
                'name' => 'apellidoPaterno',
                'sortField' => 'apellidoPaterno',
                'title' => 'backend.last_name'
            ],
            [
                'name' => 'nombreGrupo',
                'sortField' => 'nombreGrupo',
                'title' => 'backend.group'
            ],

            [
                'name' => '__component:translateBackend_rolesActividad',
                'title' => 'backend.role'
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'backend.office'
            ],
            // [
            //     'name' => '__component:inscripciones_aplicadas',
            //     'sortField' => 'inscripciones_aplicadas',
            //     'title' => 'Tipo Inscripción'
            // ],
            [
                'name' => '__component:estado_persona',
                'sortField' => 'estadoPersona',
                'title' => 'backend.state'
            ],
            [
                'name' => '__component:asistencia',
                'title' => 'backend.present',
                'titleClass' => 'text-center',
                'sortField' => 'presente',
                'dataClass' => 'text-center'
            ],

        ],
        'sortOrder' => [
            [
                'field' => 'fechaInscripcion',
                'sortField' => 'fechaInscripcion',
                'direction' => 'desc'
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
                'title' => 'backend.type',
                'callback' => 'getIcon'
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'backend.name',
            ],
            [
                'name' => 'linkEvaluacion',
                'sortField' => 'linkEvaluacion',
                'title' => 'backend.evaluation_link',
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'backend.role',
            ],
            [
                'name' => 'cantidad',
                'title' => 'backend.members',
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
    'jornadas' => [
        'fields' => [
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'backend.name',
            ],
            [
                'name' => 'activo',
                'sortField' => 'activo',
                'title' => 'backend.state',
            ],
            [
                'name' => 'fechaInicio',
                'title' => 'backend.start_date',
            ],
            [
                'name' => 'fechaFin',
                'title' => 'backend.end_date',
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'direction' => 'desc'
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
                'title' => 'backend.name'
            ],
            [
                'name' => 'apellido',
                'sortField' => 'apellidoPaterno',
                'title' => 'backend.last_name'
            ],
            [
                'name' => 'dni',
                'sortField' => 'dni',
                'title' => 'backend.document'
            ],
            [
                'name' => 'email',
                'sortField' => 'mail',
                'title' => 'backend.email'
            ],
            [
                'name' => 'telefono',
                'title' => 'backend.phone'
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
                'title' => 'backend.name'
            ],
            [
                'name' => 'area',
                'sortField' => 'area',
                'title' => 'backend.area'
            ],
            [
                'name' => 'oficina',
                'title' => 'backend.office'
            ],
            [
                'name' => '__component:comunidades',
                'title' => 'backend.community'
            ],
            [
                'name' => 'estado',
                'sortField' => 'estado',
                'title' => 'backend.state'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'nombre',
                'sortField' => 'Equipo.nombre',
                'direction' => 'asc'
            ],
        ]
    ],
    'comunidad_integrantes' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idIntegrante',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'backend.name'
            ],
            [
                'name' => 'nombreEquipo',
                'sortField' => 'nombreEquipo',
                'title' => 'backend.team',
            ],
            [
                'name' => 'despliegue',
                'sortField' => 'despliegue',
                'title' => 'backend.deployment'
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'backend.role'
            ],
            [
                'name' => 'relacion',
                'sortField' => 'relacion',
                'title' => 'backend.relation'
            ],
            [
                'name' => 'estado',
                'sortField' => 'estado',
                'title' => 'backend.state'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'title' => 'backend.start_date'
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

    'comunidad_redes' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idRedComunidad',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'backend.name'
            ],
            [
                'name' => 'tipo',
                'sortField' => 'tipo',
                'title' => 'backend.type',
            ],
            [
                'name' => 'presencia',
                'sortField' => 'presencia',
                'title' => 'backend.presencia'
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


    'comunidad_referentes' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idReferenteComunidad',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'backend.name'
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'backend.role',
            ],
            [
                'name' => 'telefono',
                'sortField' => 'telefono',
                'title' => 'backend.phone',
            ],
            [
                'name' => 'estado',
                'sortField' => 'estado',
                'title' => 'backend.state'
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

    'reuniones_equipo' => [
        'fields' => [
            [
                'name' => 'idReunion',
                'sortField' => 'idReunion',
                'visible' => false
            ],
            [
                'name' => 'idReunion',
                'sortField' => 'idReunion',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'backend.name'
            ],
            [
                'name' => 'despliegue',
                'sortField' => 'despliegue',
                'title' => 'backend.deployment'
            ],
            [
                'name' => 'fecha',
                'sortField' => 'fecha',
                'title' => 'backend.date'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'fecha',
                'sortField' => 'fecha',
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
                'title' => 'backend.name'
            ],
            [
                'name' => 'despliegue',
                'sortField' => 'despliegue',
                'title' => 'backend.deployment'
            ],
            [
                'name' => 'comunidad',
                'title' => 'backend.community',
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'backend.role',
            ],
            [
                'name' => 'relacion',
                'sortField' => 'relacion',
                'title' => 'backend.relationship'
            ],
            [
                'name' => '__component:participacion',
                'title' => 'Semaforo'
            ],
            [
                'name' => 'fechaInicio',
                'sortField' => 'fechaInicio',
                'title' => 'backend.start_date'
            ],
            [
                'name' => 'estado',
                'sortField' => 'estado',
                'title' => 'backend.state'
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

    'comunidades' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idComunidad',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'backend.name'
            ],
            [
                'name' => 'oficina',
                'title' => 'backend.office'
            ],
            [
                'name' => 'estado',
                'sortField' => 'activo',
                'title' => 'backend.state'
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
            [
                'name' => 'oficina.nombre',
                'title' => 'Oficina'
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
    'institucionEducativa' => [
        'fields' => [
            [
                'name' => 'id',
                'sortField' => 'idInstitucionEducativa',
                'visible' => false
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'Nombre'
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
                'title' => 'backend.point'
            ],
            [
                'name' => 'horario',
                'title' => 'backend.schedule'
            ],
            [
                'name' => 'provincia',
                'title' => 'backend.province'
            ],
            [
                'name' => 'localidad',
                'title' => 'backend.location'
            ],
            [
                'name' => 'nombres',
                'title' => 'backend.name'
            ],
            [
                'name' => 'apellidoPaterno',
                'title' => 'backend.last_name'
            ],
            [
                'name' => 'estado',
                'title' => 'backend.state',
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
    'informeCierre' => [
        'fields' => [
            [
                'name' => 'idActividadInformeCierre',
                'sortField' => 'idActividadInformeCierre',
                'visible' => false
            ],
            [
                'name' => '__component:translateBackend_programa',
                'title' => 'backend.programa',
            ],
            [
                'name' => '__component:translateBackend_solucionesEntregadas',
                'title' => 'backend.soluciones_entregadas',
            ],
            [
                'name' => 'total_soluciones',
                'title' => 'Total Soluciones'
            ],
            [
                'name' => 'numero_beneficiados',
                'title' => 'Beneficiados'
            ],
        ],
        'sortOrder' => [
            [
                'field' => 'idActividadInformeCierre',
                'sortField' => 'idActividadInformeCierre',
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
            [
                'name' => 'activo',
                'sortField' => 'activo',
                'title' => 'Activo',
            ],
            [
                'name' => 'tipo_indicador',
                'sortField' => 'tipo_indicador',
                'title' => 'Tipo Indicador',
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
