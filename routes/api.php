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


Route::get('personas', 'api\PersonasController@index');
Route::get('personas/{persona}', 'api\PersonasController@show');
// Route::post('personas', 'api\PersonasController@store');
// Route::put('personas/{persona}', 'api\PersonasController@update');
// Route::delete('personas/{persona}', 'api\PersonasController@delete');

Route::get('personas/mail/{mail}', 'api\PersonasController@getPersonaxMail');

// login
Route::post('login', 'api\PersonasController@login');
// register
Route::post('register', 'api\PersonasController@register');
// forgot password
// change password

// actividades que estoy inscripto

// edit Usuario 
// delete usuario


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
