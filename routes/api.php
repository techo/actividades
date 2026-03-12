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



/////////////////////////////////
// Rutas Privadas, por Token   //
/////////////////////////////////

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {

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
    });

    // personas
    Route::get('personas/{persona}', 'api\PersonasController@show');
    Route::post('editPersona/{persona}', 'api\PersonasController@update');
    Route::post('perfil/cambiar_photo', 'ajax\UsuarioController@cambiar_photo');


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

Route::get('actividades', 'ajax\ActividadesController@index');

