<table border="0" cellpadding="0" cellspacing="0" style="text-align:center" bgcolor="#D9D9D9" width="100%">
    <tr>
        <td width="25%" align="left">
            <img src="{{ asset('/img/logo_negro_154x41.png') }}" alt="Techo Argentina" style="padding-top: 5px;" height="30">
        </td>
        <td width="50%">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="display:inline-block;padding-right:5px;padding-top:5px;line-height:0px" valign="middle">
                        <a href="https://www.facebook.com/TECHO.org/" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </td>
                    <td style="display:inline-block;padding-right:5px;padding-top:5px;line-height:0px" valign="middle" align="center">
                        <a href="https://www.instagram.com/techo_org/" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </td>
                    <td style="display:inline-block;padding-right:5px;padding-top:5px;line-height:0px" valign="middle">
                        <a href="https://www.linkedin.com/company/techo-teto/" target="_blank">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </td>
                    <td style="display:inline-block;padding-right:0px;padding-top:5px;line-height:0px" valign="middle">
                        <a href="https://twitter.com/techo" target="_blank">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                    </td>
                </tr>
            </table>
        </td>
        <td width="25%"><a href="{{ url('/desuscribirse') }}/{{ $persona->unsubscribe_token }}">@lang('email.unsuscribe')</a></td>
    </tr>
</table>