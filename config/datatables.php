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
                'name' => '__component:comunidades',
                'sortField' => 'comunidades',
                'title' => 'Comunidades'
            ],
            [
                'name' => '__component:estadoActividad',
                'title' => 'Estado Actividad'
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
                'name' => '__component:comunidades',
                'sortField' => 'comunidades',
                'title' => 'Comunidades'
            ],
            [
                'name' => '__component:estadoActividad',
                'title' => 'Estado Actividad'
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
                'name' => '__component:photoPerfil',
                'title' => '',
            ],
            [
                'name' => 'dni',
                'sortField' => 'dni',
                'title' => 'DNI/Pasaporte',
                'visible' => false
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
                'name' => '__component:translateBackend_rolesActividad',
                'title' => 'Rol'
            ],
            [
                'name' => 'oficina',
                'sortField' => 'oficina',
                'title' => 'Oficina'
            ],
            // [
            //     'name' => '__component:inscripciones_aplicadas',
            //     'sortField' => 'inscripciones_aplicadas',
            //     'title' => 'Tipo Inscripción'
            // ],
            [
                'name' => '__component:estado_persona',
                'sortField' => 'estadoPersona',
                'title' => 'Estado Voluntariado'
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
                'callback' => 'getIcon'
            ],
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'Nombre',
            ],
            [
                'name' => 'linkEvaluacion',
                'sortField' => 'linkEvaluacion',
                'title' => 'Link de Evaluación',
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
    'jornadas' => [
        'fields' => [
            [
                'name' => 'nombre',
                'sortField' => 'nombre',
                'title' => 'Nombre',
            ],
            [
                'name' => 'activo',
                'sortField' => 'activo',
                'title' => 'Estado',
            ],
            [
                'name' => 'fechaInicio',
                'title' => 'Fecha Inicio',
            ],
            [
                'name' => 'fechaFin',
                'title' => 'Fecha Fin',
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
                'name' => 'telefono',
                'title' => 'Teléfono'
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
                'title' => 'Oficina'
            ],
            [
                'name' => '__component:comunidades',
                'title' => 'Comunidades'
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
                'title' => 'Nombre'
            ],
            [
                'name' => 'nombreEquipo',
                'sortField' => 'nombreEquipo',
                'title' => 'Equipo',
            ],
            [
                'name' => 'despliegue',
                'sortField' => 'despliegue',
                'title' => 'Despliegue'
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'Rol'
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
                'title' => 'Nombre'
            ],
            [
                'name' => 'tipo',
                'sortField' => 'tipo',
                'title' => 'Tipo',
            ],
            [
                'name' => 'presencia',
                'sortField' => 'presencia',
                'title' => 'Presencia'
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
                'title' => 'Nombre'
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'Rol',
            ],
            [
                'name' => 'telefono',
                'sortField' => 'telefono',
                'title' => 'Teléfono',
            ],
            [
                'name' => 'estado',
                'sortField' => 'estado',
                'title' => 'Estado'
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
                'title' => 'Nombre'
            ],
            [
                'name' => 'despliegue',
                'sortField' => 'despliegue',
                'title' => 'Despliegue'
            ],
            [
                'name' => 'fecha',
                'sortField' => 'fecha',
                'title' => 'Fecha'
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
                'title' => 'Nombre'
            ],
            [
                'name' => 'despliegue',
                'sortField' => 'despliegue',
                'title' => 'Despliegue'
            ],
            [
                'name' => 'comunidad',
                'title' => 'Comunidad',
            ],
            [
                'name' => 'rol',
                'sortField' => 'rol',
                'title' => 'Rol'
            ],
            [
                'name' => 'relacion',
                'sortField' => 'relacion',
                'title' => 'Relación'
            ],
            [
                'name' => '__component:participacion',
                'title' => 'Semáforo'
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
                'title' => 'Nombre'
            ],
            [
                'name' => 'oficina',
                'title' => 'Oficina'
            ],
            [
                'name' => 'estado',
                'sortField' => 'activo',
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
    'informeCierre' => [
        'fields' => [
            [
                'name' => 'idActividadInformeCierre',
                'sortField' => 'idActividadInformeCierre',
                'visible' => false
            ],
            [
                'name' => '__component:translateBackend_programa',
                'title' => 'Programa',
            ],
            [
                'name' => '__component:translateBackend_solucionesEntregadas',
                'title' => 'Solución Entregada',
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
