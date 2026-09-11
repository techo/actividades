@extends('emails.template')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    {{-- Aviso de cancelación (caja de atención) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
        <tr>
            <td style="background:#fbe8ec; border-left:4px solid #c0453f; border-radius:6px; padding:16px 18px;">
                <p style="margin:0; font-size:16px; line-height:1.55; color:#2b2f36;">
                    @lang('email.activity_canceled_1')
                    <strong style="color:#0092dd;">{{$actividad->nombreActividad}}</strong>
                    de TECHO - {{$pais->nombre}}@if($actividad->show_dates && $actividad->fechaInicio) (@lang('email.begins_on') {{$actividad->fechaInicio->format('d/m/Y')}})@endif
                    @lang('email.has_been')
                    <strong style="color:#c0453f;">@lang('email.cancelada')</strong>.
                </p>
            </td>
        </tr>
    </table>

    {{-- Mensaje secundario --}}
    <p style="margin:0 0 8px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.activity_canceled_2')
    </p>

    <div style="border-top:1px solid #e6e8ec; height:1px; line-height:1px; margin:22px 0;">&nbsp;</div>

    {{-- Cierre --}}
    <p style="margin:0 0 2px; font-size:15px; color:#2b2f36;">
        @lang('email.greetings')
    </p>
    <p style="margin:0; font-size:15px; font-weight:700; color:#0092dd;">
        {{ app()->getLocale() === 'pt' ? 'TETO' : 'TECHO' }} - {{$pais->nombre}}
    </p>

@endsection
