<?php

use Illuminate\Http\Request;

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

// forgot password


// Rutas Privadas, por Token

// edit Usuario 
Route::post('editPersona/{persona}', 'api\PersonasController@update');
// delete usuario
// change password

Route::get('personas', 'api\PersonasController@index');
Route::get('personas/{persona}', 'api\PersonasController@show')->middleware('auth:api');
// Route::post('personas', 'api\PersonasController@store');
// Route::put('personas/{persona}', 'api\PersonasController@update');
// Route::delete('personas/{persona}', 'api\PersonasController@delete');

Route::get('personas/mail/{mail}', 'api\PersonasController@getPersonaxMail');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
