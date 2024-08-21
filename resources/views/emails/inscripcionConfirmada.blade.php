@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}}
    </p>
    <p>@lang('email.inscription_confirmed_1') 
        <h3>{{$inscripcion->actividad->nombreActividad}}</h3>
        @if($inscripcion->actividad->show_dates)

            @lang('email.begins_on') 
            <strong>
                {{$inscripcion->actividad->fechaInicio->format('d/m/Y H:i')}}
            </strong>
        @endif
        @if($inscripcion->actividad->show_location)
            @lang('email.begins_at') 
            <strong>
                @if($inscripcion->actividad->idLocalidad)
                    {{$inscripcion->actividad->localidad->localidad}}, 
                @endif
                {{$inscripcion->actividad->provincia->provincia}}
            </strong>
        @endif
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

    <p>
      <strong>
          @lang('email.coordinator_message'):
      </strong>
        {{$inscripcion->actividad->mensajeInscripcion}}
    </p>

    @if($inscripcion->actividad->chat_grupal_whatsapp)
        <a class="btn rounded-pill text-white bg-success" href="{{ $actividad->chat_grupal_whatsapp }}" target="_blank">
            <i class="fa fa-whatsapp fa-lg" aria-hidden="true"></i>
            <span>{{ __('frontend.group_chat') }}</span>
        </a>
    @endif


    @if($inscripcion->punto_encuentro && $inscripcion->actividad->show_location)
        <p>
          <strong>
              @lang('frontend.meeting_points')
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
                    @lang('frontend.referring'):
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

    <p>
          @lang('email.greetings')
    </p>
    TECHO - {{$inscripcion->actividad->pais->nombre}}

@endsection
