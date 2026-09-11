@extends('emails.template')

@php($esBrasil = app()->getLocale() === 'pt')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$inscripcion->persona->nombres}}
    </p>

    {{-- Aviso: venció el plazo de pago (caja de atención) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
        <tr>
            <td style="background:#fbe8ec; border-left:4px solid #c0453f; border-radius:6px; padding:16px 18px;">
                <p style="margin:0 0 6px; font-size:15px; line-height:1.55; color:#2b2f36;">
                    @lang('email.payment_outdated_1')
                    @if($inscripcion->actividad->fechaLimitePago)
                        <strong>{{ $inscripcion->actividad->fechaLimitePago->format('d/m/Y') }}</strong>.
                    @endif
                </p>
                <p style="margin:0; font-size:16px; font-weight:700; color:#c0453f;">
                    @lang('email.payment_outdated_2')
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.payment_outdated_3')
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

    {{-- Cierre --}}
    <p style="margin:0 0 2px; font-size:15px; color:#2b2f36;">
        @lang('email.greetings')
    </p>
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        {{ $esBrasil ? 'TETO' : 'TECHO' }} - {{$inscripcion->actividad->pais->nombre}}
    </p>

@endsection
