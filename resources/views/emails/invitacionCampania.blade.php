@extends('emails.template')

@php($esBrasil = app()->getLocale() === 'pt')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello'){{ $nombre ? ' ' . $nombre : '' }},
    </p>

    {{-- Cuerpo HTML compuesto por el admin en el editor enriquecido (TinyMCE). --}}
    <div style="font-size:15px; line-height:1.55; color:#2b2f36;">{!! $mensaje !!}</div>

    @php($urlCampania = $pais ? url($pais->abreviacion . '/campania/' . $campaign->id) : null)

    {{-- Tarjeta de la campaña: nombre + botón de acción. --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:22px 0;">
        <tr>
            <td style="border:1px solid #e6e8ec; border-radius:10px; padding:20px 22px;">
                <p style="margin:0 0 {{ $urlCampania ? '18px' : '0' }}; font-size:18px; font-weight:700; color:#0092dd; word-break:break-word;">
                    {{ $campaign->nombre }}
                </p>
                @if($urlCampania)
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="center" bgcolor="#0092dd" style="border-radius:6px;">
                                <a href="{{ $urlCampania }}" target="_blank"
                                   style="display:inline-block; padding:12px 26px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; font-family: Montserrat, Arial, sans-serif;">
                                    @lang('email.invitation_cta_campaign')
                                </a>
                            </td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>

    {{-- Fallback en texto --}}
    @if($urlCampania)
        <p style="margin:0 0 22px; font-size:12px; line-height:1.5; color:#8a9099;">
            @lang('email.invitation_fallback')<br>
            <a href="{{ $urlCampania }}" style="color:#8a9099;">{{ $urlCampania }}</a>
        </p>
    @endif

    <div style="border-top:1px solid #e6e8ec; height:1px; line-height:1px; margin:22px 0;">&nbsp;</div>

    {{-- Cierre --}}
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        @lang('email.greetings')
    </p>

@endsection
