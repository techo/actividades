<?php
use Illuminate\Support\Facades\Auth;


Route::get('/', 'HomeController@index')->name('home');

Route::get('/actividades', 'ActividadesController@index');

Route::prefix('ajax')->group(function(){
    Route::get('provincias/{id}', 'ajax\ProvinciasController@show');
    Route::get('categorias', 'ajax\CategoriasController@index');
    Route::get('categorias/{id}', 'ajax\CategoriasController@show');
    Route::get('categorias/{id}/tipos', 'ajax\CategoriasController@tipos');

    Route::prefix('paises')->group(function(){
        Route::get('/', 'ajax\PaisesController@index');
		Route::get('{id_pais}/provincias', 'ajax\PaisesController@provincias');
		Route::get('{id_pais}/provincia/{id_provincia}/localidades', 'ajax\PaisesController@localidades');
	});
	Route::prefix('usuario')->group(function(){
		Route::get('', function(){
			if(Auth::check()) {
				return Auth::user();
			}
			return '';
		});
		Route::post('', 'ajax\UsuarioController@create');
		Route::get('valid_new_mail', 'ajax\UsuarioController@validar_nuevo_mail');
		Route::put('linkear', 'ajax\UsuarioController@linkear');
	});
    Route::post('actividades/provincias', 'ajax\ActividadesController@filtrarProvinciasYLocalidades');
    Route::post('actividades/tipos', 'ajax\ActividadesController@filtrarTiposDeActividades');
    Route::post('actividades', 'ajax\ActividadesController@index');
    Route::get('actividades/{id}', 'ajax\ActividadesController@show');
});


Route::get('/registro', function(Request $request){
    $request = request();
    if(url('/registro') != $request->headers->get('referer')) $request->session()->put('login_callback', $request->headers->get('referer'));
    return view('registro');
})->middleware('guest');

Route::get('/actividades/{id}', 'ActividadesController@show');

Route::get('/inscripciones/actividad/{id}', 'Inscripciones@puntoDeEncuentro');
Route::post('/inscripciones/actividad/{id}/confirmar', 'InscripcionesController@confirmar');;
Route::post('/inscripciones/actividad/{id}/gracias', 'InscripcionesController@create');
Route::get('/inscripciones/actividad/{id}/inscripto', 'InscripcionesController@inscripto');


Auth::routes();
Route::get('/auth/{provider}','Auth\LoginController@redirectToProvider');
Route::get('/auth/{provider}/callback','Auth\LoginController@callbackFromProvider');

Route::get('autenticado', function() {
    return (Auth::check()) ? 'si' : 'no';
});

Route::get('/usuario/verificar_mail/{token}', 'Auth\RegisterController@verificar_mail');

Route::prefix('/admin')->group(function(){
    Route::get('/actividades', 'backoffice\ActividadesController@index');
    Route::get('/actividades/{id}', 'backoffice\ActividadesController@show');
    Route::get('/ajax/actividades', 'backoffice\ajax\ActividadesController@index');
    Route::get('/ajax/unidadesOrganizacionales', 'backoffice\ajax\UnidadOrganizacionalController@index');
});