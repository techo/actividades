@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    <p>
        @lang('email.evaluation_1') 
        <strong>{{$actividad->nombreActividad}}</strong> - TECHO
        - {{$actividad->pais->nombre}}
        @if($inscripcion->actividad->show_dates)

            @lang('email.begins_on') {{$actividad->fechaInicio->format('d/m/Y')}} 
        @endif
            
        @if($inscripcion->actividad->show_location)
            @lang('email.begins_at')
        {{$actividad->localidad->localidad}}, {{$actividad->provincia->provincia}}.
        @endif
    </p>

    <p>@lang('email.evaluation_2') </p>
    <p>
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            <img src="{{ asset('/img/boton_evaluar.png') }}" alt="Ir a evaluaciones">
        </a>
    </p>

    @if($linkEvaluacionGrupal)
        <p> @lang('email.evaluation_2_2') <a href="{{ $linkEvaluacionGrupal }}">
                @lang('frontend.here') 
            </a>
        </p>
    @endif

    @if($actividad->linkEvaluacion)
        <p> @lang('email.evaluation_2_1') <a href="{{ $actividad->linkEvaluacion }}">
                @lang('frontend.here')  
            </a>
        </p>
    @endif
    <p>
       @lang('email.evaluation_3') 
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}">
            {{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}
        </a>
    </p>
@endsection