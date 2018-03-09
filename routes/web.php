<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Actividad;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('home');
});
Route::get('/actividades', 'ActividadesController@index');
Route::get('/poc', function(){
    return view('actividades.index');
});
Route::get('/actividades/{id}', function($id){
	$actividad = Actividad::find($id);
    return view('actividades.show')->with('actividad', $actividad);
});

Route::get('/inscripciones/actividad/{id}', function($id){
	$actividad = Actividad::find($id);
    return view('inscripciones.puntos_encuentro')->with('actividad', $actividad);
});

Route::post('/inscripciones/actividad/{id}/confirmar', 'InscripcionesController@confirmar');;

Route::post('/inscripciones/actividad/{id}/gracias', 'InscripcionesController@create');

Route::get('/inscripciones/actividad/{id}/inscripto', function($id){
	if(Auth::check() && Auth::user()->estaInscripto($id)) {
		return Array('idActividad' => $id);
	}
	return Array('idActividad' => false);
});

Route::get('/ajax/actividades', 'ajax\ActividadesController@index');
Route::get('/ajax/actividades/{id}', 'ajax\ActividadesController@show');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('autenticado', function() {
    return (Auth::check()) ? 'si' : 'no';
});
