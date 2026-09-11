@extends('emails.template')

@php($esBrasil = app()->getLocale() === 'pt')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 6px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}}
    </p>

    {{-- Intro + nombre de la actividad --}}
    <p style="margin:0 0 8px; font-size:15px; color:#5b616e;">
        @lang('email.missing_payment_1')
    </p>
    <p style="margin:0 0 8px; font-size:22px; line-height:1.25; font-weight:700; color:#0092dd; word-break:break-word;">
        {{$inscripcion->actividad->nombreActividad}}
    </p>

    @if($inscripcion->actividad->show_dates || $inscripcion->actividad->show_location)
        <p style="margin:0; font-size:15px; line-height:1.5; color:#2b2f36;">
            @if($inscripcion->actividad->show_dates)
                @lang('email.begins_on')
                <strong>{{$inscripcion->actividad->fechaInicio->format('d/m/Y H:i')}}</strong>
            @endif
            @if($inscripcion->actividad->show_location)
                @lang('email.begins_at')
                <strong>@if($inscripcion->actividad->idLocalidad){{$inscripcion->actividad->localidad->localidad}}, @endif{{$inscripcion->actividad->provincia->provincia}}</strong>
            @endif
        </p>
    @endif

    {{-- Aviso: falta el pago para confirmar (caja de atención ámbar) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0;">
        <tr>
            <td style="background:#fff6e6; border-left:4px solid #ff9900; border-radius:6px; padding:16px 18px;">
                <p style="margin:0 0 6px; font-size:16px; font-weight:700; color:#b56b00;">
                    @lang('email.missing_payment_2')
                </p>
                <p style="margin:0; font-size:15px; line-height:1.55; color:#2b2f36;">
                    @lang('email.missing_payment_3')
                    @if($inscripcion->actividad->fechaLimitePago)
                        <strong>{{$inscripcion->actividad->fechaLimitePago->format('d/m/Y')}}</strong>
                    @else
                        <strong>{{$inscripcion->actividad->fechaFinInscripciones->format('d/m/Y')}}</strong>
                    @endif
                </p>
            </td>
        </tr>
    </table>

    {{-- Detalle del pago --}}
    <p style="margin:0 0 8px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.missing_payment_4')
        <strong>@lang('email.confirm_by_donation')</strong>
    </p>

    @if($actividad->descripcionPago)
        <p style="margin:0 0 16px; font-size:15px; line-height:1.55; color:#5b616e;">
            {!! $actividad->descripcionPago !!}
        </p>
    @endif

    {{-- Botón: aportar / confirmar pagando (bulletproof) --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px;">
        <tr>
            <td align="center" bgcolor="#0092dd" style="border-radius:6px;">
                <a href="{{ url('inscripciones/actividad/' . $inscripcion->actividad->idActividad . '/confirmar/donacion') }}" target="_blank"
                   style="display:inline-block; padding:12px 26px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; font-family: Montserrat, Arial, sans-serif;">
                    @lang('email.confirm_by_donation')
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.missing_payment_5')
        <strong>{{ number_format($inscripcion->actividad->montoMin,0) }} {{$inscripcion->actividad->moneda}}</strong>,
        @lang('email.missing_payment_6')
    </p>
    <p style="margin:0 0 8px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.missing_payment_7')
        @if(!empty($inscripcion->actividad->beca))
            <a href="{{ $inscripcion->actividad->beca }}" style="color:#0092dd;">@lang('frontend.ask_for_grant')</a>.
        @else
            @lang('email.missing_payment_8')
        @endif
    </p>

    <div style="border-top:1px solid #e6e8ec; height:1px; line-height:1px; margin:22px 0;">&nbsp;</div>

    {{-- Coordinador/a --}}
    @if($inscripcion->actividad->coordinador)
        <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#8a9099;">
            @lang('frontend.coordinator')
        </p>
        <p style="margin:0 0 22px; font-size:15px; color:#2b2f36;">
            {{$inscripcion->actividad->coordinador->nombres}} {{$inscripcion->actividad->coordinador->apellidoPaterno}}
            &nbsp;&middot;&nbsp;
            <a href="mailto:{{ $inscripcion->actividad->coordinador->mail }}" target="_blank" style="color:#0092dd; text-decoration:none;">{{ $inscripcion->actividad->coordinador->mail }}</a>
        </p>
    @endif

    {{-- Punto de encuentro --}}
    @if($inscripcion->punto_encuentro && $inscripcion->actividad->show_location)
        <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#8a9099;">
            @lang('frontend.meeting_points')
        </p>
        <p style="margin:0 0 18px; font-size:15px; line-height:1.5; color:#2b2f36;">
            {{$inscripcion->punto_encuentro->punto}} ({{ \Illuminate\Support\Str::limit($inscripcion->punto_encuentro->horario, 5, '') }}hs)
            @if($inscripcion->punto_encuentro->idLocalidad){{$inscripcion->punto_encuentro->localidad->localidad}}, @endif{{$inscripcion->punto_encuentro->provincia->provincia}}, {{$inscripcion->punto_encuentro->pais->nombre}}
        </p>

        @if($inscripcion->punto_encuentro->responsable)
            <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#8a9099;">
                @lang('frontend.referring')
            </p>
            <p style="margin:0 0 22px; font-size:15px; color:#2b2f36;">
                {{$inscripcion->punto_encuentro->responsable->nombres}} {{$inscripcion->punto_encuentro->responsable->apellidoPaterno}}
                &nbsp;&middot;&nbsp;
                <a href="mailto:{{ $inscripcion->punto_encuentro->responsable->mail }}" target="_blank" style="color:#0092dd; text-decoration:none;">{{ $inscripcion->punto_encuentro->responsable->mail }}</a>
            </p>
        @endif
    @endif

    {{-- Cierre --}}
    <p style="margin:0 0 2px; font-size:15px; color:#2b2f36;">
        @lang('email.greetings')
    </p>
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        {{ $esBrasil ? 'TETO' : 'TECHO' }} - {{$inscripcion->actividad->pais->nombre}}
    </p>

@endsection
