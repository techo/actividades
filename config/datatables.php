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
            // [
            //   name' => 'nickname',
            //   sortField' => 'nickname',
            //   callback' => 'allcap'
            // ],
            // [
            [
                'name' => '__component:custom-actions',
                'title' => 'Acciones',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center'
            ]
        ],
        'sortOrder' => [
            [
             'field' => 'nombreActividad',
                'sortField' => 'nombreActividad',
                'direction' => 'asc'
            ]
        ],
    ]
];
