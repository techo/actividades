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
    /*
     * Listado con columnas configurables (ver config/listados.php):
     *  - fijas:    siempre visibles, no aparecen en el selector de columnas.
     *  - catalogo: campos opcionales agrupados; cada uno lleva una `key` estable
     *              que se usa en el selector y en las preferencias persistidas.
     *  - defaults: keys visibles cuando el usuario todavía no guardó preferencias
     *              (equivalen a las columnas históricas del listado).
     * Los campos condicionales (confirma/pago según la actividad) y los grupos
     * dinámicos (preguntas, ficha médica, seguimiento) los arma InscripcionesCatalogo.
     */
    'inscripciones' => [
        'fijas' => [
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
                'name' => 'nombres',
                'sortField' => 'nombres',
                'title' => 'backend.name'
            ],
            [
                'name' => 'apellidoPaterno',
                'sortField' => 'apellidoPaterno',
                'title' => 'backend.last_name'
            ],
        ],
        'catalogo' => [
            'datos_generales' => [
                [
                    'key' => 'dni',
                    'name' => 'dni',
                    'sortField' => 'dni',
                    'title' => 'backend.dni'
                ],
                [
                    'key' => 'telefonoMovil',
                    'name' => 'telefonoMovil',
                    'title' => 'backend.mobile'
                ],
                [
                    'key' => 'mail',
                    'name' => 'mail',
                    'sortField' => 'mail',
                    'title' => 'backend.email'
                ],
                [
                    'key' => 'edad',
                    'name' => 'fechaNacimiento',
                    'sortField' => 'fechaNacimiento',
                    'title' => 'backend.age',
                    'callback' => 'edad'
                ],
                [
                    'key' => 'genero',
                    'name' => 'genero',
                    'sortField' => 'genero',
                    'title' => 'backend.gender'
                ],
                [
                    'key' => 'pPais',
                    'name' => 'pPais',
                    'title' => 'backend.country'
                ],
                [
                    'key' => 'pProvincia',
                    'name' => 'pProvincia',
                    'title' => 'backend.province'
                ],
                [
                    'key' => 'pLocalidad',
                    'name' => 'pLocalidad',
                    'title' => 'backend.locality'
                ],
                [
                    'key' => 'oficina',
                    'name' => 'oficina',
                    'sortField' => 'oficina',
                    'title' => 'backend.office'
                ],
                [
                    'key' => 'fechaInscripcion',
                    'name' => 'fechaInscripcion',
                    'sortField' => 'fechaInscripcion',
                    'title' => 'backend.registration_date',
                    'callback' => 'formatDate|DD/MM/YYYY HH:mm'
                ],
                [
                    'key' => 'punto',
                    'name' => 'punto',
                    'title' => 'backend.meeting_point'
                ],
                [
                    'key' => 'jornadas',
                    'name' => 'jornadas',
                    'title' => 'backend.days'
                ],
                [
                    'key' => 'tipoInscripcion',
                    'name' => '__component:inscripciones_aplicadas',
                    'title' => 'backend.inscription_type'
                ],
                [
                    'key' => 'nombreGrupo',
                    'name' => 'nombreGrupo',
                    'sortField' => 'nombreGrupo',
                    'title' => 'backend.group'
                ],
                [
                    'key' => 'rolesActividad',
                    'name' => '__component:translateBackend_rolesActividad',
                    'title' => 'backend.role'
                ],
                [
                    'key' => 'estado_persona',
                    'name' => '__component:estado_persona',
                    'sortField' => 'estadoPersona',
                    'title' => 'backend.state'
                ],
                [
                    'key' => 'asistencia',
                    'name' => '__component:asistencia',
                    'title' => 'backend.present',
                    'titleClass' => 'text-center',
                    'sortField' => 'presente',
                    'dataClass' => 'text-center'
                ],
            ],
            'ficha_medica' => [
                [
                    'key' => 'grupo_sanguinieo',
                    'name' => 'grupo_sanguinieo',
                    'title' => 'backend.blood_group'
                ],
                [
                    'key' => 'cobertura_nombre',
                    'name' => 'cobertura_nombre',
                    'title' => 'backend.medical_coverage'
                ],
                [
                    'key' => 'cobertura_numero',
                    'name' => 'cobertura_numero',
                    'title' => 'backend.medical_coverage_number'
                ],
                [
                    'key' => 'contacto_nombre',
                    'name' => 'contacto_nombre',
                    'title' => 'backend.emergency_contact'
                ],
                [
                    'key' => 'contacto_telefono',
                    'name' => 'contacto_telefono',
                    'title' => 'backend.emergency_contact_phone'
                ],
                [
                    'key' => 'contacto_relacion',
                    'name' => 'contacto_relacion',
                    'title' => 'backend.emergency_contact_relation'
                ],
                [
                    'key' => 'alergias',
                    'name' => 'alergias',
                    'title' => 'backend.allergies'
                ],
                [
                    'key' => 'vacunacion_covid',
                    'name' => 'vacunacion_covid',
                    'title' => 'backend.covid_vaccination'
                ],
                [
                    'key' => 'alimentacion',
                    'name' => 'alimentacion',
                    'title' => 'backend.diet'
                ],
            ],
        ],
        'defaults' => ['nombreGrupo', 'rolesActividad', 'oficina', 'estado_persona', 'asistencia'],
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

    // Listado con columnas configurables (ver nota en la sección 'inscripciones').
    'integrantes' => [
        'fijas' => [
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
        ],
        'catalogo' => [
            'datos_generales' => [
                [
                    'key' => 'despliegue',
                    'name' => 'despliegue',
                    'sortField' => 'despliegue',
                    'title' => 'backend.deployment'
                ],
                [
                    'key' => 'comunidad',
                    'name' => 'comunidad',
                    'title' => 'backend.community',
                ],
                [
                    'key' => 'rol',
                    'name' => 'rol',
                    'sortField' => 'rol',
                    'title' => 'backend.role',
                ],
                [
                    'key' => 'cargo',
                    'name' => 'cargo',
                    'sortField' => 'cargo',
                    'title' => 'backend.cargo',
                ],
                [
                    'key' => 'relacion',
                    'name' => 'relacion',
                    'sortField' => 'relacion',
                    'title' => 'backend.relationship'
                ],
                [
                    'key' => 'participacion',
                    'name' => '__component:participacion',
                    'title' => 'Semaforo'
                ],
                [
                    'key' => 'fechaInicio',
                    'name' => 'fechaInicio',
                    'sortField' => 'fechaInicio',
                    'title' => 'backend.start_date'
                ],
                [
                    'key' => 'fechaFin',
                    'name' => 'fechaFin',
                    'sortField' => 'fechaFin',
                    'title' => 'backend.end_date'
                ],
                [
                    'key' => 'estado',
                    'name' => 'estado',
                    'sortField' => 'estado',
                    'title' => 'backend.state'
                ],
                [
                    'key' => 'descripcion_rol',
                    'name' => 'descripcion_rol',
                    'title' => 'backend.role_description'
                ],
                [
                    'key' => 'meta',
                    'name' => 'meta',
                    'title' => 'backend.goal'
                ],
                [
                    'key' => 'hitos',
                    'name' => 'hitos',
                    'title' => 'backend.milestones'
                ],
                [
                    'key' => 'dia_hora_reunion',
                    'name' => 'dia_hora_reunion',
                    'title' => 'backend.meeting_day_and_time'
                ],
                [
                    'key' => 'periodicidad_reunion',
                    'name' => 'periodicidad_reunion',
                    'title' => 'backend.meeting_frequency'
                ],
                [
                    'key' => 'impacto',
                    'name' => 'impacto',
                    'title' => 'backend.impact'
                ],
                [
                    'key' => 'capacidades',
                    'name' => 'capacidades',
                    'title' => 'backend.capabilities'
                ],
            ],
        ],
        'defaults' => ['despliegue', 'comunidad', 'rol', 'relacion', 'participacion', 'fechaInicio', 'estado'],
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
    'campanas' => [
        'fields' => [
            [
                'name'      => 'id',
                'sortField' => 'id',
                'visible'   => false,
            ],
            [
                'name'      => 'nombre',
                'sortField' => 'nombre',
                'title'     => 'backend.name',
            ],
            [
                'name'      => 'tipo',
                'sortField' => 'tipo',
                'title'     => 'backend.type',
            ],
            [
                'name'      => 'activa',
                'sortField' => 'activa',
                'title'     => 'backend.state',
            ],
            [
                'name'      => 'created_at',
                'sortField' => 'created_at',
                'title'     => 'backend.created_at',
                'callback'  => 'formatDate|DD-MM-YYYY',
            ],
        ],
        'sortOrder' => [
            [
                'field'     => 'created_at',
                'sortField' => 'created_at',
                'direction' => 'desc',
            ],
        ],
    ],

    'campana_suscriptos' => [
        'fields' => [
            [
                'name'      => 'id',
                'sortField' => 'id',
                'visible'   => false,
            ],
            [
                'name'      => 'nombre',
                'sortField' => 'nombre',
                'title'     => 'backend.name',
            ],
            [
                'name'      => 'apellido',
                'sortField' => 'apellido',
                'title'     => 'backend.last_name',
            ],
            [
                'name'      => 'mail',
                'sortField' => 'mail',
                'title'     => 'backend.email',
            ],
            [
                'name'      => 'telefono',
                'sortField' => 'telefono',
                'title'     => 'backend.phone',
            ],
            [
                'name'      => 'convertido',
                'sortField' => 'convertido',
                'title'     => 'backend.converted',
            ],
        ],
        'sortOrder' => [
            [
                'field'     => 'created_at',
                'sortField' => 'created_at',
                'direction' => 'desc',
            ],
        ],
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
