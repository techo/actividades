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



// Rutas Publicas
Route::post('login', 'api\PersonasController@login');
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

    Route::post('logout', 'api\PersonasController@logout');
    Route::get('actividades', 'ajax\ActividadesController@index');

    // personas
    //Route::get('personas', 'api\PersonasController@index');
    Route::get('personas/{persona}', 'api\PersonasController@show');
    Route::post('editPersona/{persona}', 'api\PersonasController@update');
   // Route::get('personas/mail/{mail}', 'api\PersonasController@getPersonaxMail');

    Route::get('inscripciones/', 'api\PersonasController@getInscripciones');
});




