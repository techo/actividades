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
    <hr>
    @if($inscripcion->actividad->coordinador)
        <p>
            <strong>@lang('frontend.coordinator'):</strong>
        </p>
        <p>
            {{$inscripcion->actividad->coordinador->nombres}} {{$inscripcion->actividad->coordinador->apellidoPaterno}}
            <a href="mailto:{{ $inscripcion->actividad->coordinador->mail }}" target="_blank">
                {{ $inscripcion->actividad->coordinador->mail }}
            </a>
        </p><hr>
    @endif

    <p>
      <strong>
          @lang('email.coordinator_message'):
      </strong>
        {{$inscripcion->actividad->mensajeInscripcion}}
    </p>
    <hr>
    @if($inscripcion->actividad->chat_grupal_whatsapp)
        <a class="btn rounded-pill text-white bg-success" href="{{ $inscripcion->actividad->chat_grupal_whatsapp }}" target="_blank">
            <i class="fa fa-whatsapp fa-lg" aria-hidden="true"></i>
            <span>{{ __('frontend.group_chat') }} Whatsapp</span>
        </a>
    <hr>
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
        <hr>
    @endif
    
    @isset($QRCode)
    
            <div class="row justify-content-center text-center">
                <div class="col-md-12">
                    <h5>{{ __('frontend.confirm_inscription_with_qr') }}</h5>
                    <span>{{ __('frontend.show_on_arrival') }}</span>
                </div>
            </div>
            <div class="d-flex justify-content-center m-2">
                {!! $QRCode !!}
            </div>
            <hr class="mx-auto" style="width: 80%;">
    @endif
    <p>
          @lang('email.greetings')
    </p>
    TECHO - {{$inscripcion->actividad->pais->nombre}}

@endsection
