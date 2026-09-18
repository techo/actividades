<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\Exception\OAuthServerException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // 401 esperado, no un error de la app: la app mobile pega a la API con un
        // token vencido/revocado y Passport (TokenGuard::authenticateViaBearerToken)
        // reporta la OAuthServerException como ERROR. Era ~el 95% del ruido del log
        // de prod (sep-2026). AuthenticationException ("Unauthenticated.") ya viene
        // silenciada por el handler base de Laravel. Nota: si en el futuro se usa el
        // grant password/authorization_code por /oauth/token, conviene un filtro más
        // fino (solo error access_denied) para no ocultar serverError de emisión.
        OAuthServerException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)){
            app('sentry')->captureException($exception);
        }
        
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if($exception instanceof AuthenticationException){
            return $this->unauthenticated($request, $exception);
        }
        if($exception instanceof TokenMismatchException){
            return $this->tokenMismatch($request, $exception);
        }
        return parent::render($request, $exception);
    }

    /**
     * Token CSRF vencido (419 "Página expirada"). No es una excepción de la app,
     * por eso Laravel no lo loguea: acá lo registramos como warning para poder
     * medir el impacto (cuántos, en qué rutas) y servimos una vista amigable que
     * reintenta con token fresco en vez del "Whoops" genérico.
     */
    protected function tokenMismatch($request, TokenMismatchException $exception)
    {
        Log::warning('CSRF 419 TokenMismatch', [
            'url'     => $request->fullUrl(),
            'method'  => $request->method(),
            'referer' => $request->header('referer'),
            'user'    => optional($request->user())->idPersona,
            'ip'      => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error'   => 'Page Expired',
                'message' => 'Tu sesión expiró. Recargá la página e intentá de nuevo.',
            ], 419);
        }

        return response()->view('errors.419', [
            'retryUrl' => $this->csrfRetryUrl($request),
        ], 419);
    }

    /**
     * URL a la que conviene volver tras un 419 para reintentar con token fresco.
     * Para el flujo de inscripción, el inicio del flujo (GET) re-renderiza el token;
     * si no, el referer; y como último recurso, el home.
     */
    protected function csrfRetryUrl($request)
    {
        if (preg_match('#/inscripciones/actividad/(\d+)#', $request->path(), $m)) {
            return '/inscripciones/actividad/' . $m[1];
        }
        return $request->header('referer') ?: '/';
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($request->hasHeader('referer')){
           $afterLoginUrl = $request->header('referer');
        } else {
            $afterLoginUrl = $request->getUri();
        }

        return redirect('/login')->cookie('after_login_url', $afterLoginUrl, 10);
    }
}
