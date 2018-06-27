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

@endsection