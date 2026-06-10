@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{ $inscripcion->persona->nombres }}
    </p>
    <p>
        @lang('email.voucher_rechazado_intro') <strong>{{ $inscripcion->actividad->nombreActividad }}</strong>.
    </p>
    <p>
        <strong>
            <span style="color:rgb(200,50,50)">@lang('email.voucher_rechazado_estado')</span>
        </strong>
    </p>

    @if($motivo)
    <p>
        <strong>@lang('email.voucher_rechazado_motivo_label'):</strong><br>
        {{ $motivo }}
    </p>
    @endif

    <p>
        @lang('email.voucher_rechazado_instruccion')
    </p>

    <p>
        <a href="{{ url('inscripciones/actividad/' . $inscripcion->actividad->idActividad . '/confirmar/donacion') }}">
            @lang('email.voucher_rechazado_link')
        </a>
    </p>

    @if($inscripcion->actividad->coordinador)
        <p>
            <strong>@lang('frontend.coordinator'):</strong>
        </p>
        <p>
            {{ $inscripcion->actividad->coordinador->nombres }} {{ $inscripcion->actividad->coordinador->apellidoPaterno }}
            <a href="mailto:{{ $inscripcion->actividad->coordinador->mail }}" target="_blank">
                {{ $inscripcion->actividad->coordinador->mail }}
            </a>
        </p>
    @endif

    <p>@lang('email.greetings')</p>
    TECHO - {{ $inscripcion->actividad->pais->nombre }}

@endsection
