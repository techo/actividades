@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}},
    </p>

    <p>
        @lang('email.activity_reminder_1')
        <strong>{{$inscripcion->actividad->nombreActividad}}</strong> - TECHO
        - {{$inscripcion->actividad->pais->nombre}}
        @lang('email.begins_on') {{$inscripcion->actividad->fechaInicio->format('d/m/Y H:i')}} @lang('email.begins_at') 
        {{$inscripcion->actividad->localidad->localidad}}, {{$inscripcion->actividad->provincia->provincia}}.           @if($inscripcion->actividad->idLocalidad)
            {{$inscripcion->actividad->localidad->localidad}}, 
        @endif
        {{$inscripcion->actividad->provincia->provincia}}, 
        {{$inscripcion->actividad->pais->nombre}}.
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
    @if($inscripcion->punto_encuentro)
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
        </p>
        <p>
            <strong>
                @lang('email.hour'):
            </strong>
        </p>
        <p>
            {{ str_limit($inscripcion->punto_encuentro->horario, 5, '')}}
        </p>

        @if($inscripcion->punto_encuentro->responsable)
            <p>
                <strong>
                    @lang('frontend.referring'):
                </strong>
            </p>
            <p>
                {{$inscripcion->punto_encuentro->responsable->nombres}}
                {{$inscripcion->punto_encuentro->responsable->apellidoPaterno}}
                <a href="mailto:{{ $inscripcion->punto_encuentro->responsable->mail }}" target="_blank">
                    {{ $inscripcion->punto_encuentro->responsable->mail }}
                </a>

            </p>
        @endif
    @endif

@endsection