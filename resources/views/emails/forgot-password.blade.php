@extends('emails.template')

@section('content')

    {{-- Saludo --}}
    <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#2b2f36;">
        @lang('frontend.hello')
    </p>

    <p style="margin:0 0 20px; font-size:15px; line-height:1.55; color:#2b2f36;">
        @lang('email.forgot_password_1')
    </p>

    {{-- Botón: restablecer contraseña (bulletproof) --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
        <tr>
            <td align="center" bgcolor="#0092dd" style="border-radius:6px;">
                <a href="{{ url('password/reset', $token) }}" target="_blank"
                   style="display:inline-block; padding:12px 28px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:6px; font-family: Montserrat, Arial, sans-serif;">
                    @lang('email.forgot_password_link')
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; line-height:1.55; color:#8a9099;">
        @lang('email.forgot_password_2')
    </p>

@endsection
