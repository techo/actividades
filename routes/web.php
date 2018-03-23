<?php
use App\Actividad;
use Illuminate\Support\Facades\Auth;


Route::get('/', 'HomeController@index')->name('home');

Route::get('/actividades', 'ActividadesController@index');

Route::get('/poc', function(){
    return view('actividades.index');
});

Route::prefix('ajax')->group(function(){
	Route::get('paises', 'ajax\PaisesController@index');
	Route::get('provincias/{id}', 'ajax\ProvinciasController@show');
	Route::get('categorias/{id}', 'ajax\CategoriasController@show');
	Route::prefix('pais')->group(function(){
		Route::get('{id_pais}/provincias', 'ajax\PaisesController@provincias');
		Route::get('{id_pais}/provincia/{id_provincia}/localidades', 'ajax\PaisesController@localidades');
	});
	Route::prefix('usuario')->group(function(){
		Route::post('', 'ajax\UsuarioController@create');
		Route::get('valid_new_mail', 'ajax\UsuarioController@validar_nuevo_mail');
	});
	Route::get('actividades', 'ajax\ActividadesController@index');
	Route::get('actividades/{id}', 'ajax\ActividadesController@show');
});


Route::get('/registro', function(){
    return view('registro');
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

Auth::routes();
Route::get('/auth/{provider}','Auth\LoginController@redirectToProvider');
Route::get('/auth/{provider}/callback','Auth\LoginController@callbackFromProvider');

Route::get('autenticado', function() {
    return (Auth::check()) ? 'si' : 'no';
});
