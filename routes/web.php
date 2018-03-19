<?php
use App\Actividad;
use Illuminate\Support\Facades\Auth;


Route::get('/', 'HomeController@index')->name('home');

Route::get('/actividades', 'ActividadesController@index');

Route::get('/poc', function(){
    return view('actividades.index');
});

Route::get('/registro', function(){
    return view('registro');
});

Route::get('/actividades/{id}', function($id){
	$actividad = Actividad::find($id);
    return view('actividades.show')->with('actividad', $actividad);
});


Route::post('/ajax/usuario', 'ajax\UsuarioController@create');
Route::get('/ajax/usuario/valid_new_mail', 'ajax\UsuarioController@validar_nuevo_mail');
Route::get('/ajax/paises', 'ajax\PaisesController@index');
Route::get('/ajax/pais/{id_pais}/provincias', 'ajax\PaisesController@provincias');
Route::get('/ajax/pais/{id_pais}/provincia/{id_provincia}/localidades', 'ajax\PaisesController@localidades');
Route::get('/ajax/categorias/{id}', 'ajax\CategoriasController@show');
Route::get('/ajax/provincias/{id}', 'ajax\ProvinciasController@show');
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

Route::get('autenticado', function() {
    return (Auth::check()) ? 'si' : 'no';
});
