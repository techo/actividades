@extends('emails.template')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    <p style="margin:0 0 20px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.verificar_mail_1')
    </p>

    {{-- Botón: verificar email (bulletproof) --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px;">
        <tr>
            <td align="center" bgcolor="#0092dd" style="border-radius:6px;">
                <a href="{{ $url_verificacion }}" target="_blank"
                   style="display:inline-block; padding:12px 28px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; font-family: Montserrat, Arial, sans-serif;">
                    @lang('email.email_verification')
                </a>
            </td>
        </tr>
    </table>

@endsection
