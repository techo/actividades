@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    <p>
        @lang('email.evaluation_1') 
        <strong>{{$actividad->nombreActividad}}</strong> - TECHO
        - {{$actividad->pais->nombre}}
        @lang('email.begins_on') {{$actividad->fechaInicio->format('d/m/Y')}} @lang('email.begins_at')
        {{$actividad->localidad->localidad}}, {{$actividad->provincia->provincia}}.
    </p>

    <p>@lang('email.evaluation_2') </p>
    <p>
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            <img src="{{ asset('/img/boton_evaluar.png') }}" alt="Ir a evaluaciones">
        </a>
    </p>

    {% if($actividad->linkEvaluacion) %}
        <p> @lang('email.evaluation_2_1') <a href="{{ $actividad->linkEvaluacion }}">
                aquí 
            </a>
        </p>
    {% end if %}
    <p>
       @lang('email.evaluation_3') 
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            {{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}
        </a>
    </p>
@endsection