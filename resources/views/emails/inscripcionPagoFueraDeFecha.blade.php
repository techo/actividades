@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}}
    </p>
    <p>
        @lang('email.payment_outdated_1') 

        @if($inscripcion->actividad->fechaLimitePago)
            {{ $inscripcion->actividad->fechaLimitePago }}.
        @endif
    </p>
    <p> 
        <b>
            @lang('email.payment_outdated_2')
        </b>.
    </p>
    <p>
        @lang('email.payment_outdated_3')
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

    <p>@lang('email.greetings')</p>
    TECHO - {{$inscripcion->actividad->pais->nombre}}

@endsection
