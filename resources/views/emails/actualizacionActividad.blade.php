@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}},
    </p>

    <p>
        @lang('email.activity_update_1')
    </p>
    <p>
        <strong>{{$inscripcion->actividad->nombreActividad}}</strong> - TECHO
       
       - {{$inscripcion->actividad->pais->nombre}},
       
        @if($inscripcion->actividad->show_dates)
            @lang('email.begins_on') {{$inscripcion->actividad->fechaInicio->format('d/m/Y')}} 
        @endif

        @if($inscripcion->actividad->show_location)
            @lang('email.begins_at') {{$inscripcion->actividad->provincia->provincia}}.
        @endif
    </p>

    @if($inscripcion->actividad->coordinador)
        <p>
            <strong>@lang('frontend.coordinator'):</strong>
            {{$inscripcion->actividad->coordinador->nombres}} {{$inscripcion->actividad->coordinador->apellidoPaterno}}
            <a href="mailto:{{ $inscripcion->actividad->coordinador->mail }}" target="_blank">
                {{ $inscripcion->actividad->coordinador->mail }}
            </a>
        </p>
    @endif

    <p>
        {{$inscripcion->actividad->mensajeInscripcion}}
    </p>

    <p>
        <strong>@lang('email.greetings')</strong>
    </p>
    @if($inscripcion->punto_encuentro && $inscripcion->actividad->show_location)
        <p>
            <strong>
                @lang('frontend.meeting_points')
            </strong>
        </p>
        <p>
            {{$inscripcion->punto_encuentro->punto}}, 
            @if($inscripcion->punto_encuentro->idLocalidad)
                {{$inscripcion->punto_encuentro->localidad->localidad}}, 
            @endif
            {{$inscripcion->punto_encuentro->provincia->provincia}},
            {{$inscripcion->punto_encuentro->pais->nombre}} 
            - 
            {{ str_limit($inscripcion->punto_encuentro->horario, 5, '')}}hs 

        @if($inscripcion->punto_encuentro->responsable)
            (@lang('frontend.referring'): 
                {{$inscripcion->punto_encuentro->responsable->nombres}}
                {{$inscripcion->punto_encuentro->responsable->apellidoPaterno}}
                <a href="mailto:{{ $inscripcion->punto_encuentro->responsable->mail }}" target="_blank">
                    {{ $inscripcion->actividad->coordinador->mail }}
                </a>)
        @endif
        </p>
    @endif

@endsection