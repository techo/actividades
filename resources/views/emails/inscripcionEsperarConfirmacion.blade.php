@extends('emails.template')

@php($esBrasil = app()->getLocale() === 'pt')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 6px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}}
    </p>

    {{-- Intro + nombre de la actividad --}}
    <p style="margin:0 0 8px; font-size:15px; color:#5b616e;">
        @lang('email.pre_enroll_1')
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

    {{-- Estado: a la espera de confirmación (caja destacada) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0;">
        <tr>
            <td style="background:#eef7fc; border-left:4px solid #0092dd; border-radius:6px; padding:14px 16px;">
                <p style="margin:0 0 4px; font-size:15px; font-weight:700; color:#0092dd;">
                    @lang('email.pre_enroll_2')
                </p>
                <p style="margin:0; font-size:15px; line-height:1.55; color:#2b2f36;">
                    @lang('email.pre_enroll_3')
                </p>
            </td>
        </tr>
    </table>

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

    {{-- Referente del punto de encuentro --}}
    @if($inscripcion->punto_encuentro && $inscripcion->actividad->show_location && $inscripcion->punto_encuentro->responsable)
        <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#8a9099;">
            @lang('frontend.referring')
        </p>
        <p style="margin:0 0 22px; font-size:15px; color:#2b2f36;">
            {{$inscripcion->punto_encuentro->responsable->nombres}} {{$inscripcion->punto_encuentro->responsable->apellidoPaterno}}
            &nbsp;&middot;&nbsp;
            <a href="mailto:{{ $inscripcion->punto_encuentro->responsable->mail }}" target="_blank" style="color:#0092dd; text-decoration:none;">{{ $inscripcion->punto_encuentro->responsable->mail }}</a>
        </p>
    @endif

    <div style="border-top:1px solid #e6e8ec; height:1px; line-height:1px; margin:22px 0;">&nbsp;</div>

    {{-- Cierre --}}
    <p style="margin:0 0 2px; font-size:15px; color:#2b2f36;">
        @lang('email.greetings')
    </p>
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        {{ $esBrasil ? 'TETO' : 'TECHO' }} - {{$inscripcion->actividad->pais->nombre}}
    </p>

@endsection
