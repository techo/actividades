@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}}
    </p>
    <p>@lang('email.pre_enroll_1') <strong>{{$inscripcion->actividad->nombreActividad}}</strong>
        @lang('email.begins_on')
            <strong>{{$inscripcion->actividad->localidad->localidad}}, {{$inscripcion->actividad->provincia->provincia}}</strong>               <strong>
                {{$inscripcion->actividad->fechaInicio->format('d/m/Y H:i')}}
            </strong> @lang('email.begins_at')
            <strong>
                @if($inscripcion->actividad->idLocalidad)
                    {{$inscripcion->actividad->localidad->localidad}}, 
                @endif
                {{$inscripcion->actividad->provincia->provincia}}
            </strong>
    </p>

    <p>
        <strong>@lang('email.pre_enroll_2')</strong>
        @lang('email.pre_enroll_3')
    </p>

    @if($inscripcion->actividad->coordinador)
        <p>
            <strong>@lang('frontend.coordinator'):</strong>
        </p>
        <p>
            {{$inscripcion->actividad->coordinador->nombres}} {{$inscripcion->actividad->coordinador->apellidoPaterno}}
            <a href="mailto:{{ $inscripcion->actividad->coordinador->mail }}" target="_blank">
                {{ $inscripcion->actividad->coordinador->mail }}
            </a>
        </p>
    @endif

    @if($inscripcion->punto_encuentro)
        <p>
          <strong>
              @lang('frontend.meeting_points'):
          </strong>
            {{$inscripcion->punto_encuentro->punto}} ({{ str_limit($inscripcion->punto_encuentro->horario, 5, '')}}hs)
            @if($inscripcion->punto_encuentro->idLocalidad)
                {{$inscripcion->punto_encuentro->localidad->localidad}},
            @endif
            {{$inscripcion->punto_encuentro->provincia->provincia}}, {{$inscripcion->punto_encuentro->pais->nombre}}
        </p>

        @if($inscripcion->punto_encuentro->responsable)
            <p>
                <strong>
                    @lang('frontend.referring')::
                </strong>
            </p>
            <p>
                {{$inscripcion->punto_encuentro->responsable->nombres}}
                {{$inscripcion->punto_encuentro->responsable->apellidoPaterno}}
                <p><a href="mailto:{{ $inscripcion->punto_encuentro->responsable->mail }}" target="_blank">
                    {{ $inscripcion->punto_encuentro->responsable->mail }}
                </a></p>
            </p>
        @endif
    @endif

    <p>@lang('email.greetings')</p>
    TECHO - {{$inscripcion->actividad->pais->nombre}}

@endsection
