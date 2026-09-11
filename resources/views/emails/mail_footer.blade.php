@php($esBrasil = app()->getLocale() === 'pt')

<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#e9ebee" style="border-radius:6px;">
    <tr>
        <td align="center" style="padding:18px 20px;">

            {{-- Logo --}}
            <img src="{{ asset($esBrasil ? '/img/logo_negro_154x41_pt.png' : '/img/logo_negro_154x41.png') }}" alt="{{ $esBrasil ? 'TETO' : 'TECHO' }}" height="26" style="display:block; border:0; margin:0 auto 10px;">

            {{-- Redes (links de texto: los iconos de fuente no renderizan en clientes de mail) --}}
            <p style="margin:0 0 8px; font-size:12px; line-height:1.6; color:#6b7280;">
                <a href="https://www.facebook.com/TECHO.org/" target="_blank" style="color:#4c4d4f; text-decoration:none;">Facebook</a>
                &nbsp;&middot;&nbsp;
                <a href="https://www.instagram.com/techo_org/" target="_blank" style="color:#4c4d4f; text-decoration:none;">Instagram</a>
                &nbsp;&middot;&nbsp;
                <a href="https://www.linkedin.com/company/techo-teto/" target="_blank" style="color:#4c4d4f; text-decoration:none;">LinkedIn</a>
                &nbsp;&middot;&nbsp;
                <a href="https://twitter.com/techo" target="_blank" style="color:#4c4d4f; text-decoration:none;">X</a>
            </p>

            {{-- Baja: depende de una Persona con token. Algunos envíos (p.ej. leads de
                 campaña sin cuenta) no tienen Persona; en ese caso no se muestra el link. --}}
            @if(!empty($persona) && !empty($persona->unsubscribe_token))
                <p style="margin:0; font-size:12px; line-height:1.5;">
                    <a href="{{ url('/desuscribirse') }}/{{ $persona->unsubscribe_token }}" style="color:#8a9099; text-decoration:underline;">@lang('email.unsuscribe')</a>
                </p>
            @endif

        </td>
    </tr>
</table>
