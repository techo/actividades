<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/translate', 'api\TranslationController@getTranslation');
Route::post('/translate/batch', 'api\TranslationController@getBatchTranslations');


// ── Donations — Stripe webhook (no auth, signature-validated by Stripe) ──────
// Must be declared BEFORE the auth:api group so it is not auth-protected.
// API routes are already CSRF-exempt (VerifyCsrfToken does not run for api group).
Route::post(
    'donations/stripe/webhook',
    'api\DonationWebhookController@handle'
)->name('api.donations.webhook');

// Rutas Publicas
Route::post('login', 'api\PersonasController@login');
Route::post('socialLogin', 'api\PersonasController@socialLogin');
Route::post('providerLogin', 'api\PersonasController@providerLogin');
Route::post('register', 'api\PersonasController@register');
Route::post('create', 'ajax\UsuarioController@apiCreate');

// forgot password
Route::post('resetPassword', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('categorias', 'ajax\CategoriasController@index');
Route::get('/sedes', 'backoffice\ajax\OficinasController@getOficinas');

Route::prefix('paises')->group(function () {
    Route::get('/', 'ajax\PaisesController@index');
    Route::get('{id_pais}/provincias', 'ajax\PaisesController@provincias');
    Route::get('{id_pais}/provincias/{id_provincia}/localidades', 'ajax\PaisesController@localidades');
    Route::get('/habilitados', 'ajax\PaisesController@paisesHabilitados');
});


Route::get('actividadesGeneral', 'ajax\ActividadesController@index');

// ── Campañas (público) ────────────────────────────────────────────────────────
Route::prefix('campanas')->group(function () {
    Route::get('/',      'api\CampanasController@index');
    Route::get('/{id}',  'api\CampanasController@show');
});

/////////////////////////////////
// Rutas Privadas, por Token   //
/////////////////////////////////

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {

    Route::post('ping', 'api\PersonasController@ping');

    Route::get('actividades', 'ajax\ActividadesController@index');

    Route::post('password/reset', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');

    Route::delete('usuario', 'ajax\UsuarioController@delete'); //Anonimiza cuenta de usuario

    Route::post('logout', 'api\PersonasController@logout');

    Route::prefix('perfil')->group(function () {
        Route::post('fichaMedica', 'ajax\FichaMedicaController@upsert');
        Route::get('fichaMedica', 'ajax\FichaMedicaController@getFichaMedica');
        Route::post('fichaMedica/archivo_medico', 'ajax\FichaMedicaController@uploadArchivoMedico');

        Route::prefix('estudios')->group(function () {
            Route::post('', 'ajax\EstudiosController@create');
            Route::get('', 'ajax\EstudiosController@index');
            Route::put('', 'ajax\EstudiosController@update');
            Route::delete('/{id}', 'ajax\EstudiosController@delete');

            Route::prefix('institucionEducativa')->group(function () {
                Route::get('', 'ajax\InstitucionEducativaController@index');
                Route::get('{idInstitucionEducativa}', 'ajax\InstitucionEducativaController@get');
                Route::get('pais/{idPais}', 'ajax\InstitucionEducativaController@porPais');
            });
        });
    });


    Route::prefix('actividades')->group(function () {
        Route::get('/{id}', 'ajax\ActividadesController@show');

        Route::prefix('/{id}/evaluaciones')->group(function () {
            Route::get('', 'EvaluacionesController@index');
            Route::get('/tags', 'EvaluacionesController@getTagsActividad');
            
            Route::post('', 'EvaluacionesController@evaluarActividad');
            Route::post('/persona/{idPersona}', 'EvaluacionesController@evaluarPersona');
            Route::post('/impacto', 'EvaluacionesController@evaluarImpacto');
        });
    });

    Route::prefix('inscripciones')->group(function () {
        Route::get('', 'ajax\UsuarioController@inscripciones');
        Route::delete('{id}', 'ajax\UsuarioController@desinscribir');
        Route::post('/actividad/{id}', 'InscripcionesController@create');
        Route::post('/voucher', 'InscripcionesController@voucherPago');
        Route::post('/voucher/clear', 'InscripcionesController@clearVoucher');
        Route::post('/beca', 'InscripcionesController@becaSolicitud');
    });

    // personas
    Route::get('personas/{persona}', 'api\PersonasController@show');
    Route::post('editPersona/{persona}', 'api\PersonasController@update');
    Route::post('perfil/cambiar_photo', 'ajax\UsuarioController@cambiar_photo');

    // ── Campañas (autenticado) ────────────────────────────────────────────────
    Route::prefix('campanas')->group(function () {
        Route::post('/{id}/suscribir',  'api\CampanasController@suscribir');
        Route::get('/{id}/suscripcion', 'api\CampanasController@suscripcion');
    });

    // dispositivos para notificaciones push (OneSignal)
    Route::post('dispositivos', 'api\DispositivoController@registrar');
    Route::delete('dispositivos/{player_id}', 'api\DispositivoController@desactivar');

    // ── Donations ─────────────────────────────────────────────────────────
    Route::prefix('donations')->group(function () {
        // Config de checkout: moneda y montos sugeridos según el país del usuario
        Route::get('stripe/checkout-config', 'api\DonationController@checkoutConfig')
             ->name('api.donations.checkout-config');

        // One-time: create PaymentIntent and persist a donation record
        Route::post('stripe/payment-intent', 'api\DonationController@createPaymentIntent')
             ->name('api.donations.create-intent');

        // Recurring: create Stripe Subscription and persist the record
        Route::post('stripe/subscription', 'api\DonationController@createSubscription')
             ->name('api.donations.create-subscription');

        // List active subscriptions for the authenticated user
        Route::get('stripe/subscription', 'api\DonationController@listSubscriptions')
             ->name('api.donations.list-subscriptions');

        // Update amount/interval of an existing subscription
        Route::patch('stripe/subscription/{subscriptionId}', 'api\DonationController@updateSubscription')
             ->name('api.donations.update-subscription');

        // Create a Stripe Customer Portal session (self-manage subscriptions)
        Route::post('stripe/billing-portal', 'api\DonationController@billingPortal')
             ->name('api.donations.billing-portal');

        // Poll subscription status by Stripe Subscription ID
        Route::get('stripe/subscription/{subscriptionId}/status', 'api\DonationController@getSubscriptionStatus')
             ->name('api.donations.subscription-status');

        // Poll one-time donation status by Stripe PaymentIntent ID
        Route::get('{intentId}/status', 'api\DonationController@getStatus')
             ->name('api.donations.status');

        // Unified donation + subscription history
        Route::get('history', 'api\DonationController@history')
             ->name('api.donations.history');
    });

    // ── Inscripcion Stripe (mobile payment for activity enrollment) ───────────
    Route::post(
        'inscripciones/{idInscripcion}/stripe/payment-intent',
        'api\InscripcionStripeController@createPaymentIntent'
    )->name('api.inscripciones.stripe.payment-intent');


    Route::get('actividades/categoria/{nombre}', function ($nombre, Request $request) {
        $categorias = [
            'construcciones' => [11, 27, 65, 72, 73, 80, 81, 98, 105, 114, 115],
            'mesas'          => [25, 28, 29, 75, 76, 82, 83, 85, 113, 117],
            'infraestructura' => [22, 32, 77, 79, 97, 103],
            'formativos'     => [23, 30, 31, 33, 34, 35, 36, 45, 46, 47, 49, 52, 53, 56, 58, 59, 62, 89],
            'encuentros'     => [54, 55, 63, 64, 68, 69, 71, 86, 88, 90],
            'colecta'        => [43, 96],
            'eventos'        => [44, 48, 60, 101, 104],
        ];

        if (!isset($categorias[$nombre])) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }
        $tipos = collect($categorias[$nombre])
            ->map(function ($id) {
                return ['idTipo' => $id];
            })
            ->toArray();

        $request->merge(['tipos' => json_encode($tipos)]);

        return app(\App\Http\Controllers\ajax\ActividadesController::class)->index($request);
    });
});

// ── API de reporting (read-only sobre las vistas reporting_*) ────────────────
// Puerta de entrada para Power BI y otros consumidores sin acceso directo a BD.
// Detrás de auth:api (Passport). Hoy sin scope por país: trae todo.
Route::middleware('auth:api')->prefix('reporting')->group(function () {
    Route::get('catalog', 'api\reporting\ReportingController@catalog');
    Route::get('datasets/{name}', 'api\reporting\ReportingController@dataset');
    // metrics: rutas específicas antes del genérico {key}.
    Route::get('metrics', 'api\reporting\ReportingController@metricsCatalog');
    Route::get('metrics/movilizacion', 'api\reporting\ReportingController@movilizacion');
    Route::get('metrics/{key}', 'api\reporting\ReportingController@metric');
});


