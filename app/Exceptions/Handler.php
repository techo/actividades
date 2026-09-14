<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
        return parent::render($request, $exception);
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
