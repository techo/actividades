<?php
use App\Actividad;
use Illuminate\Support\Facades\Auth;


Route::get('/', 'HomeController@index')->name('home');

Route::get('/actividades', 'ActividadesController@index');

Route::get('/poc', function(){
    return view('actividades.index');
});
Route::get('/actividades/{id}', function($id){
	$actividad = Actividad::find($id);
    return view('actividades.show')->with('actividad', $actividad);
});

Route::get('/ajax/categorias/{id}', 'ajax\CategoriasController@show');
Route::get('/ajax/provincias/{id}', 'ajax\ProvinciasController@show');
Route::post('/ajax/actividades/provincias', 'ajax\ActividadesController@filtrarProvinciasYLocalidades');
Route::post('/ajax/actividades/tipos', 'ajax\ActividadesController@filtrarTiposDeActividades');

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

Route::post('/ajax/actividades', 'ajax\ActividadesController@index');
Route::get('/ajax/actividades/{id}', 'ajax\ActividadesController@show');
Auth::routes();

Route::get('autenticado', function() {
    return (Auth::check()) ? 'si' : 'no';
});
