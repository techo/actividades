<?php
use Illuminate\Support\Facades\Auth;
use App\Actividad;

Route::get('/', 'HomeController@index')->name('home');

Route::get('/actividades', 'ActividadesController@index');

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
    Route::post('actividades/provincias', 'ajax\ActividadesController@filtrarProvinciasYLocalidades');
    Route::post('actividades/tipos', 'ajax\ActividadesController@filtrarTiposDeActividades');
    Route::post('actividades', 'ajax\ActividadesController@index');
    Route::get('actividades/{id}', 'ajax\ActividadesController@show');
});

Route::get('/registro', function(){
    return view('registro');
})->middleware('guest');

Route::get('/actividades/{id}', 'ActividadesController@show');

Route::get('/inscripciones/actividad/{id}', function($id){
	$actividad = Actividad::find($id);
    return view('inscripciones.seleccionar_puntos_encuentro')->with('actividad', $actividad);
})->middleware('requiere.auth');
Route::post('/inscripciones/actividad/{id}/confirmar', 'InscripcionesController@confirmar');

Route::post('/inscripciones/actividad/{id}/gracias', 'InscripcionesController@create');
Route::get('/inscripciones/actividad/{id}/inscripto', 'InscripcionesController@inscripto');


Auth::routes();

Route::get('autenticado', function() {
    return (Auth::check()) ? 'si' : 'no';
});

Route::get('/usuario/verificar_mail/{token}', 'Auth\RegisterController@verificar_mail');
