@extends('emails.template')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{ $report->reporter_name ?: '' }}
    </p>

    {{-- Intro según el tipo de aviso --}}
    <p style="margin:0 0 20px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @if($esResuelto)
            @lang('email.reporte_resuelto_intro')
        @else
            @lang('email.reporte_respondido_intro')
        @endif
    </p>

    {{-- Lo que la persona había reportado --}}
    <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#8a9099;">
        @lang('email.reporte_tu_reporte_label')
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
        <tr>
            <td style="background:#f3f5f7; border-left:4px solid #c3c9d0; border-radius:6px; padding:14px 16px;">
                <p style="margin:0; font-size:14px; line-height:1.55; color:#4a5159; white-space:pre-wrap;">{{ \Illuminate\Support\Str::limit($report->description, 400) }}</p>
            </td>
        </tr>
    </table>

    {{-- La respuesta del equipo (si escribió algo) --}}
    @if(!empty($reply->body))
        <p style="margin:0 0 4px; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#8a9099;">
            @if($reply->esDeTechita())
                @lang('email.reporte_respuesta_techita')
            @else
                @lang('email.reporte_respuesta_label')
            @endif
        </p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
            <tr>
                <td style="background:#e8f4fb; border-left:4px solid #0092dd; border-radius:6px; padding:14px 16px;">
                    <p style="margin:0; font-size:15px; line-height:1.55; color:#2b2f36; white-space:pre-wrap;">{{ $reply->body }}</p>
                </td>
            </tr>
        </table>
    @endif

    {{-- Estado resuelto --}}
    @if($esResuelto)
        <p style="margin:0 0 20px; font-size:16px; font-weight:700; color:#1f8a4c;">
            @lang('email.reporte_resuelto_estado')
        </p>
    @endif

    {{-- Cierre de agradecimiento --}}
    <p style="margin:0; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.reporte_cierre')
    </p>

    {{-- Firma --}}
    @if($reply->esDeTechita())
        <p style="margin:16px 0 0; font-size:15px; line-height:1.55; color:#2b2f36;">
            — @lang('email.reporte_firma_techita')
        </p>
    @endif

@endsection
