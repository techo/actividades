@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{ $persona->nombres }},
    </p>

    {{-- Cuerpo HTML compuesto por el admin en el editor enriquecido (TinyMCE). --}}
    <div>{!! $mensaje !!}</div>

    {{-- Tarjeta de la actividad: nombre + lugar/fecha + botón de acción. --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:22px 0;">
        <tr>
            <td style="border:1px solid #e3e3e3; border-radius:6px; padding:18px 20px;">
                <p style="margin:0 0 4px; font-size:16px; font-weight:700; color:#333333;">
                    {{ $actividad->nombreActividad }}
                </p>
                <p style="margin:0 0 16px; color:#777777; font-size:13px;">
                    TECHO {{ $actividad->pais->nombre }}@if($actividad->show_dates) &middot; {{ $actividad->fechaInicio->format('d/m/Y') }}@endif
                </p>
                <a href="{{ url('/actividades/' . $actividad->idActividad) }}"
                   style="display:inline-block; background-color:#0092dd; color:#ffffff; text-decoration:none; padding:11px 24px; border-radius:4px; font-weight:700; font-family: Fredoka, Montserrat, sans-serif;">
                    @lang('email.invitation_cta')
                </a>
            </td>
        </tr>
    </table>

    <p style="color:#999999; font-size:12px; margin:0 0 22px;">
        @lang('email.invitation_fallback')<br>
        <a href="{{ url('/actividades/' . $actividad->idActividad) }}" style="color:#999999;">{{ url('/actividades/' . $actividad->idActividad) }}</a>
    </p>

    <p>
        <strong>@lang('email.greetings')</strong>
    </p>

@endsection
