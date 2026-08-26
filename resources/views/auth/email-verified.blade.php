<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('messages.email_verified') }}</title>
    <style>
        :root { --azul: #005baa; --verde: #2e7d32; --texto: #1f2933; --gris: #6b7280; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #f2f5f9; color: var(--texto);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding: 24px;
        }
        .card {
            background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08);
            max-width: 420px; width: 100%; padding: 40px 28px; text-align: center;
        }
        .check {
            width: 72px; height: 72px; border-radius: 50%; background: var(--verde);
            display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
        }
        .check svg { width: 38px; height: 38px; stroke: #fff; stroke-width: 3; fill: none; }
        h1 { font-size: 20px; margin: 0 0 10px; }
        p { color: var(--gris); font-size: 15px; line-height: 1.5; margin: 0 0 24px; }
        .btn {
            display: inline-block; background: var(--azul); color: #fff; text-decoration: none;
            padding: 14px 22px; border-radius: 10px; font-weight: 600; font-size: 15px;
        }
        .btn:active { opacity: .85; }
        .hint { margin-top: 18px; font-size: 13px; color: var(--gris); }
    </style>
</head>
<body>
    <div class="card">
        <div class="check" aria-hidden="true">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h1>{{ __('messages.email_verified') }}</h1>
        <p>Ya podés volver a la app MiTECHO. Si no se abre sola, tocá el botón.</p>

        <a id="openApp" class="btn" href="{{ $appDeepLink }}">Abrir MiTECHO</a>

        <div class="hint">Si te registraste desde la web, ya podés iniciar sesión con tu cuenta.</div>
    </div>

    <script>
        (function () {
            var deepLink = @json($appDeepLink);
            // Intento de apertura automática de la app (si está instalada). Es
            // inofensivo en desktop / si el esquema no está registrado: queda la
            // página de éxito con el botón como alternativa.
            setTimeout(function () {
                try { window.location.href = deepLink; } catch (e) {}
            }, 600);
        })();
    </script>
</body>
</html>
