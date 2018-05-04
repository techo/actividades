<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body>
<style>
    @import url('https://fonts.googleapis.com/css?family=Montserrat');

    @media only screen and (max-width: 600px) {
        .inner-body {
            width: 100% !important;
        }

        .footer {
            width: 100% !important;
        }
    }

    @media only screen and (max-width: 500px) {
        .button {
            width: 100% !important;
        }
    }
</style>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" style="font-family: Montserrat, sans-serif">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr bgcolor="#0092dd">
                    <td align="left">
                        <a href="{{ url('/') }}">
                            <img src="{{ url('img/techo-logo_269x83.png') }}" alt="Techo" width="170">
                        </a>
                        {{--@yield('header')--}}
                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
                                    @yield('content')
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p style="font-size: smaller; color: #4c4d4f">Para TECHO - Argentina es importante
                                        que te
                                        mantengas enterado de las nuevas actividades.
                                        Para ello, entra siempre en <a href="{{ url('/') }}">nuestro Sitio Web.</a>
                                    </p>
                                    <p> ¡Muchas gracias!</p>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <table class="footer" align="center" width="100%" cellpadding="5" cellspacing="0"
                               bgcolor="#d9d9d9">
                            <tr>
                                <td class="content-cell" align="center">
                                    @include('emails.mail_footer')
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>