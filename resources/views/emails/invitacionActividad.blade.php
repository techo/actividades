@extends('emails.template')

@php($esBrasil = app()->getLocale() === 'pt')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{ $persona->nombres }},
    </p>

    {{-- Cuerpo HTML compuesto por el admin en el editor enriquecido (TinyMCE). --}}
    <div style="font-size:15px; line-height:1.55; color:#2b2f36;">{!! $mensaje !!}</div>

    {{-- Tarjeta de la actividad: nombre + lugar/fecha + botón de acción. --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:22px 0;">
        <tr>
            <td style="border:1px solid #e6e8ec; border-radius:10px; padding:20px 22px;">
                <p style="margin:0 0 4px; font-size:18px; font-weight:700; color:#0092dd; word-break:break-word;">
                    {{ $actividad->nombreActividad }}
                </p>
                <p style="margin:0 0 18px; color:#8a9099; font-size:13px;">
                    {{ $esBrasil ? 'TETO' : 'TECHO' }} {{ optional($actividad->pais)->nombre }}@if($actividad->show_dates) &middot; {{ $actividad->fechaInicio->format('d/m/Y') }}@endif
                </p>
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="center" bgcolor="#0092dd" style="border-radius:6px;">
                            <a href="{{ url('/actividades/' . $actividad->idActividad) }}" target="_blank"
                               style="display:inline-block; padding:12px 26px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; font-family: Montserrat, Arial, sans-serif;">
                                @lang('email.invitation_cta')
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Fallback en texto --}}
    <p style="margin:0 0 22px; font-size:12px; line-height:1.5; color:#8a9099;">
        @lang('email.invitation_fallback')<br>
        <a href="{{ url('/actividades/' . $actividad->idActividad) }}" style="color:#8a9099;">{{ url('/actividades/' . $actividad->idActividad) }}</a>
    </p>

    <div style="border-top:1px solid #e6e8ec; height:1px; line-height:1px; margin:22px 0;">&nbsp;</div>

    {{-- Cierre --}}
    <p style="margin:0 0 2px; font-size:15px; color:#2b2f36;">
        @lang('email.greetings')
    </p>
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        {{ $esBrasil ? 'TETO' : 'TECHO' }} - {{ optional($actividad->pais)->nombre }}
    </p>

@endsection
