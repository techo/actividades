@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello'){{ $nombre ? ' ' . $nombre : '' }},
    </p>

    {{-- Cuerpo HTML compuesto por el admin en el editor enriquecido (TinyMCE). --}}
    <div>{!! $mensaje !!}</div>

    @php($urlCampania = $pais ? url($pais->abreviacion . '/campania/' . $campaign->id) : null)

    {{-- Tarjeta de la campaña: nombre + botón de acción. --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:22px 0;">
        <tr>
            <td style="border:1px solid #e3e3e3; border-radius:6px; padding:18px 20px;">
                <p style="margin:0 0 {{ $urlCampania ? '16px' : '0' }}; font-size:16px; font-weight:700; color:#333333;">
                    {{ $campaign->nombre }}
                </p>
                @if($urlCampania)
                    <a href="{{ $urlCampania }}"
                       style="display:inline-block; background-color:#0092dd; color:#ffffff; text-decoration:none; padding:11px 24px; border-radius:4px; font-weight:700; font-family: Fredoka, Montserrat, sans-serif;">
                        @lang('email.invitation_cta_campaign')
                    </a>
                @endif
            </td>
        </tr>
    </table>

    @if($urlCampania)
        <p style="color:#999999; font-size:12px; margin:0 0 22px;">
            @lang('email.invitation_fallback')<br>
            <a href="{{ $urlCampania }}" style="color:#999999;">{{ $urlCampania }}</a>
        </p>
    @endif

    <p>
        <strong>@lang('email.greetings')</strong>
    </p>

@endsection
