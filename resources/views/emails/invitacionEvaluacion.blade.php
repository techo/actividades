@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        Hola {{$persona->nombres}},
    </p>

    <p>
        Queremos saber cómo te fue en
        <strong>{{$actividad->nombreActividad}}</strong> de TECHO
        - {{$actividad->pais->nombre}}
        que inició el {{$actividad->fechaInicio->format('d/m/Y')}} en
        {{$actividad->localidad->localidad}}, {{$actividad->provincia->provincia}}.
    </p>

    <p>Para darnos tu opinión, haz click en el enlace </p>
    <p>
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            <img src="{{ asset('/img/boton_evaluar.png') }}" alt="Ir a evaluaciones">
        </a>
    </p>

    <p>
        Si no puedes usar el enlace, copia y pega esta dirección en tu navegador:
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            {{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}
        </a>
    </p>
@endsection