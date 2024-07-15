@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    <p>
        @lang('email.evaluation_1') 
        <strong>{{$actividad->nombreActividad}}</strong> - TECHO
        - {{$actividad->pais->nombre}}
        @if($actividad->show_dates)
            @lang('email.begins_on') {{$actividad->fechaInicio->format('d/m/Y')}} 
        @endif
            
        @if($actividad->show_location)
            @lang('email.begins_at')
            {{ $actividad->localidad->localidad}}, {{$actividad->provincia->provincia}}.
        @endif
    </p>

    <p>@lang('email.evaluation_2') </p>
    <p>
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            <img src="{{ asset('/img/boton_evaluar.png') }}" alt="Ir a evaluaciones">
        </a>
    </p>

    @if($grupo->linkEvaluacion != '')
        <p> @lang('email.evaluation_2_2') <a href="{{ ($grupo->linkEvaluacion }}">
                @lang('frontend.here') 
            </a>
        </p>
    @endif

        <p> @lang('email.evaluation_2_1') <a href="{{ $actividad->linkEvaluacion }}">
                @lang('frontend.here')  
            </a>
        </p>
    <p>
       @lang('email.evaluation_3') 
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            {{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}
        </a>
    </p>
@endsection