@extends('emails.template')

@php($esBrasil = app()->getLocale() === 'pt')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 6px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    {{-- Intro + nombre de la actividad --}}
    <p style="margin:0 0 8px; font-size:15px; color:#5b616e;">
        @lang('email.evaluation_1')
    </p>
    <p style="margin:0 0 8px; font-size:22px; line-height:1.25; font-weight:700; color:#0092dd; word-break:break-word;">
        {{$actividad->nombreActividad}}
    </p>

    @if($actividad->show_dates || $actividad->show_location)
        <p style="margin:0; font-size:15px; line-height:1.5; color:#2b2f36;">
            @if($actividad->show_dates)
                @lang('email.begins_on')
                <strong>{{$actividad->fechaInicio->format('d/m/Y')}}</strong>
            @endif
            @if($actividad->show_location)
                @lang('email.begins_at')
                <strong>{{optional($actividad->localidad)->localidad}}, {{optional($actividad->provincia)->provincia}}</strong>
            @endif
        </p>
    @endif

    <p style="margin:22px 0 16px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.evaluation_2')
    </p>

    {{-- Botón: ir a evaluaciones (bulletproof) --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px;">
        <tr>
            <td align="center" bgcolor="#0092dd" style="border-radius:6px;">
                <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}" target="_blank"
                   style="display:inline-block; padding:12px 26px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; font-family: Montserrat, Arial, sans-serif;">
                    @lang('email.evaluation_2')
                </a>
            </td>
        </tr>
    </table>

    {{-- Fallback en texto --}}
    <p style="margin:0 0 22px; font-size:12px; line-height:1.5; color:#8a9099;">
        @lang('email.evaluation_3')<br>
        <a href="{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}" style="color:#8a9099;">{{ url('/actividades/'. $actividad->idActividad .'/evaluaciones') }}</a>
    </p>

    <div style="border-top:1px solid #e6e8ec; height:1px; line-height:1px; margin:22px 0;">&nbsp;</div>

    {{-- Cierre --}}
    <p style="margin:0 0 2px; font-size:15px; color:#2b2f36;">
        @lang('email.greetings')
    </p>
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        {{ $esBrasil ? 'TETO' : 'TECHO' }} - {{optional($actividad->pais)->nombre}}
    </p>

@endsection
