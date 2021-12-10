<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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


Route::get('/sedes', 'backoffice\ajax\OficinasController@getOficinas');
// forgot password

Route::post('resetPassword', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');

// Rutas Privadas, por Token

// edit Usuario 
Route::post('editPersona/{persona}', 'api\PersonasController@update')->middleware('auth:api');
Route::post('logout', 'api\PersonasController@logout')->middleware('auth:api');

// delete usuario
// change password

Route::get('personas', 'api\PersonasController@index')->middleware('auth:api');
Route::get('personas/{persona}', 'api\PersonasController@show')->middleware('auth:api');
// Route::post('personas', 'api\PersonasController@store');
// Route::put('personas/{persona}', 'api\PersonasController@update');
// Route::delete('personas/{persona}', 'api\PersonasController@delete');

Route::get('personas/mail/{mail}', 'api\PersonasController@getPersonaxMail')->middleware('auth:api');
Route::get('inscripciones/', 'api\PersonasController@getInscripciones')->middleware('auth:api');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
