@extends('emails.template')

@php($esBrasil = app()->getLocale() === 'pt')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 6px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}}
    </p>

    {{-- Intro + nombre de la actividad --}}
    <p style="margin:0 0 8px; font-size:15px; color:#5b616e;">
        @lang('email.inscription_confirmed_1')
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
                <strong>@if($inscripcion->actividad->idLocalidad){{optional($inscripcion->actividad->localidad)->localidad}}, @endif{{optional($inscripcion->actividad->provincia)->provincia}}</strong>
            @endif
        </p>
    @endif

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

    {{-- Mensaje de la coordinación (caja destacada) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
        <tr>
            <td style="background:#eef7fc; border-left:4px solid #0092dd; border-radius:6px; padding:14px 16px;">
                <p style="margin:0 0 5px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#0092dd;">
                    @lang('email.coordinator_message')
                </p>
                <p style="margin:0; font-size:15px; line-height:1.55; color:#2b2f36;">
                    {{$inscripcion->actividad->mensajeInscripcion}}
                </p>
            </td>
        </tr>
    </table>

    {{-- Botón de WhatsApp (bulletproof) --}}
    @if($inscripcion->actividad->chat_grupal_whatsapp)
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
            <tr>
                <td align="center" bgcolor="#25D366" style="border-radius:24px;">
                    <a href="{{ $inscripcion->actividad->chat_grupal_whatsapp }}" target="_blank"
                       style="display:inline-block; padding:11px 24px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:24px;">
                        {{ __('frontend.group_chat') }} WhatsApp
                    </a>
                </td>
            </tr>
        </table>
    @endif

    {{-- Puntos de encuentro --}}
    @if($inscripcion->punto_encuentro && $inscripcion->actividad->show_location)
        <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#8a9099;">
            @lang('frontend.meeting_points')
        </p>
        <p style="margin:0 0 18px; font-size:15px; line-height:1.5; color:#2b2f36;">
            {{$inscripcion->punto_encuentro->punto}} ({{ \Illuminate\Support\Str::limit($inscripcion->punto_encuentro->horario, 5, '') }}hs)
            @if($inscripcion->punto_encuentro->idLocalidad){{optional($inscripcion->punto_encuentro->localidad)->localidad}}, @endif{{optional($inscripcion->punto_encuentro->provincia)->provincia}}, {{optional($inscripcion->punto_encuentro->pais)->nombre}}
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

    {{-- QR (tarjeta centrada) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding:4px 0 8px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e6e8ec; border-radius:10px;">
                    <tr>
                        <td align="center" style="padding:20px 28px;">
                            <p style="margin:0 0 4px; font-size:16px; font-weight:700; color:#2b2f36;">
                                {{ __('frontend.confirm_inscription_with_qr') }}
                            </p>
                            <p style="margin:0 0 14px; font-size:13px; color:#8a9099;">
                                {{ __('frontend.show_on_arrival') }}
                            </p>
                            <img src="{{ $message->embedData($qrCode, 'qr.png', 'image/png') }}" alt="QR" width="200" height="200" style="display:block; margin:0 auto; border-radius:8px;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="border-top:1px solid #e6e8ec; height:1px; line-height:1px; margin:22px 0;">&nbsp;</div>

    {{-- Cierre --}}
    <p style="margin:0 0 2px; font-size:15px; color:#2b2f36;">
        @lang('email.greetings')
    </p>
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        {{ $esBrasil ? 'TETO' : 'TECHO' }} - {{optional($inscripcion->actividad->pais)->nombre}}
    </p>

@endsection
