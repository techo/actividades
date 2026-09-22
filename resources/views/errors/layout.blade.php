{{--
    Layout de páginas de error AUTOCONTENIDO.

    A propósito NO extiende `main`: ese layout incluye el header, que consulta
    Auth::user() y la base. Si un 500 viene de una caída de DB, extender `main`
    haría fallar la propia página de error y caería en el "Whoops" crudo de
    Laravel. Acá todo es HTML + CSS inline y el logo va como SVG embebido, sin
    depender de assets compilados, sesión ni base.
--}}
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Ups') · TECHO</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f4f7f9;
            color: #33383b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .err-card {
            background: #fff;
            max-width: 520px;
            width: 100%;
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(0, 45, 80, .10);
            padding: 44px 36px;
            text-align: center;
        }
        .err-logo { width: 72px; height: 72px; margin: 0 auto 22px; display: block; }
        .err-code {
            font-size: 13px; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; color: #009de0; margin-bottom: 10px;
        }
        .err-title { font-size: 24px; line-height: 1.25; margin: 0 0 12px; color: #1f2937; font-weight: 700; }
        .err-msg { font-size: 15px; line-height: 1.6; color: #5b6670; margin: 0 auto 28px; max-width: 400px; }
        .err-actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .err-btn {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 15px; font-weight: 600; padding: 12px 22px;
            border-radius: 8px; text-decoration: none; border: 0; cursor: pointer;
            transition: filter .15s, background .15s;
        }
        .err-btn-primary { background: #0092dd; color: #fff; }
        .err-btn-primary:hover { filter: brightness(.94); }
        .err-btn-ghost { background: #eef2f5; color: #33383b; }
        .err-btn-ghost:hover { background: #e2e8ee; }
        .err-ref { margin-top: 24px; font-size: 12px; color: #9aa4ad; word-break: break-all; }
        @media (max-width: 420px) {
            .err-card { padding: 32px 22px; }
            .err-title { font-size: 21px; }
        }
    </style>
</head>
<body>
    <div class="err-card">
        <svg class="err-logo" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="TECHO">
            <circle cx="256" cy="256" r="256" fill="#009de0"/>
            <path d="M100.785,335.285a8,8,0,0,1-8-8v-134H1.156a7.925,7.925,0,0,1,1.916-3.074L187.628,5.657a8,8,0,0,1,11.314,0L383.5,190.211a7.928,7.928,0,0,1,1.916,3.073H292.784v134a8,8,0,0,1-8,8Z" transform="translate(63.216 62.715)" fill="#fff"/>
        </svg>
        <div class="err-code">@yield('code')</div>
        <h1 class="err-title">@yield('title')</h1>
        <p class="err-msg">@yield('message')</p>
        <div class="err-actions">
            @yield('actions')
            <a href="/" class="err-btn err-btn-ghost">{{ __('errors.back_home') }}</a>
        </div>
        @yield('below')
    </div>
</body>
</html>
