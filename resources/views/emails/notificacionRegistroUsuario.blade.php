@extends('emails.template')

@section('content')

    {{-- Título de bienvenida --}}
    <p style="margin:0 0 6px; font-size:24px; line-height:1.2; font-weight:800; color:#0092dd;">
        @lang('email.account_registration_title')
    </p>

    {{-- Saludo --}}
    <p style="margin:0 0 12px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    <p style="margin:0 0 18px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.account_registration_1')
    </p>

    {{-- Botón: verificar email (bulletproof) --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
        <tr>
            <td align="center" bgcolor="#0092dd" style="border-radius:6px;">
                <a href="{{ $url_verificacion }}" target="_blank"
                   style="display:inline-block; padding:12px 28px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; font-family: Montserrat, Arial, sans-serif;">
                    @lang('email.email_verification')
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 18px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.account_registration_2')<a href="{{ url('/actividades') }}" style="color:#0092dd;">web</a>.
    </p>

    {{-- Qué podés hacer ahora (caja destacada) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
        <tr>
            <td style="background:#eef7fc; border-left:4px solid #0092dd; border-radius:6px; padding:16px 18px;">
                <p style="margin:0 0 10px; font-size:15px; font-weight:700; color:#2b2f36;">
                    @lang('email.account_registration_3')
                </p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr><td style="padding:3px 0; font-size:15px; line-height:1.5; color:#2b2f36;">&bull;&nbsp; @lang('email.account_registration_4')</td></tr>
                    <tr><td style="padding:3px 0; font-size:15px; line-height:1.5; color:#2b2f36;">&bull;&nbsp; @lang('email.account_registration_5')</td></tr>
                    <tr><td style="padding:3px 0; font-size:15px; line-height:1.5; color:#2b2f36;">&bull;&nbsp; @lang('email.account_registration_6')</td></tr>
                    <tr><td style="padding:3px 0; font-size:15px; line-height:1.5; color:#2b2f36;">&bull;&nbsp; @lang('email.account_registration_7')</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.account_registration_8')
        <a href="{{ url('/perfil') }}" style="color:#0092dd;">@lang('email.profile')</a>
        @lang('email.account_registration_9')
        <a href="{{ url('/perfil/actividades') }}" style="color:#0092dd;">@lang('email.account_registration_10')</a>.
    </p>

@endsection
