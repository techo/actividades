<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Opciones fijas en dropdown inputs
    |--------------------------------------------------------------------------
    |
    | Simplifica la definición de opciones fijas para distintos dropdowns del
    | sitio.
    | Organización: Feature > Componente > Input > Opciones
    */
  'actividad' => [
      'filtroInscripciones' => [
          'campos' => [
            //   ['id' => 'nombre','campo' => 'Nombre', 'condiciones' => true],
            //   ['id' => 'apellido', 'campo' => 'Apellido', 'condiciones' => true],
            //   ['id' => 'dni', 'campo' => 'DNI/Pasaporte', 'condiciones' => true],
            //   ['id' => 'email', 'campo' => 'e-mail', 'condiciones' => true],
              ['id' => 'punto' , 'campo' =>  'Punto de Encuentro', 'condiciones' => true],
              ['id' => 'oficina' , 'campo' =>  'Oficina', 'condiciones' => true],
              ['id' => 'pendiente_confirmacion' , 'campo' =>  'Pendiente confirmación', 'condiciones' => false],
              ['id' => 'confirmado' , 'campo' =>  'Confirmado', 'condiciones' => false],
              ['id' => 'presente' , 'campo' =>  'Presente', 'condiciones' => false],
              ['id' => 'jornada' , 'campo' =>  'Jornada', 'condiciones' => true],
              ['id' => 'rol' , 'campo' =>  'Rol', 'condiciones' => true],
              ['id' => 'grupo' , 'campo' =>  'Grupo', 'condiciones' => true],
              ['id' => 'cantidadActividades' , 'campo' =>  'Cantidad Actividades', 'condiciones' => true],
              ['id' => 'tipoActividad' , 'campo' =>  'Tipo de Actividad Anterior', 'condiciones' => true],
              //   ['id' => 'promedioEvaluacion' , 'campo' =>  'Promedio Evaluación (No disponible)']
          ],
          'condiciones' => [
              ['value' => '>', 'label' => 'mayor que'],
              ['value' => '>=', 'label' => 'mayor o igual que'],
              ['value' => '<', 'label' => 'menor que'],
              ['value' => '<=', 'label' => 'menor o igual que'],
              ['value' => '=', 'label' => 'igual a'],
              ['value' => '<>', 'label' => 'distinto de'],
              ['value' => 'like', 'label' => 'contiene'],
              ['value' => 'in', 'label' => 'está en lista']
          ]
      ]
  ]
];