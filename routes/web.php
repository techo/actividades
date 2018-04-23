<?php

use Illuminate\Support\Facades\Auth;
use App\Actividad;

Route::get('/', 'HomeController@index')->name('home');

Route::get('/actividades', 'ActividadesController@index');

Route::prefix('ajax')->group(function () {
    Route::get('provincias/{id}', 'ajax\ProvinciasController@show');
    Route::get('categorias', 'ajax\CategoriasController@index');
    Route::get('categorias/{id}', 'ajax\CategoriasController@show');
    Route::get('categorias/{id}/tipos', 'ajax\CategoriasController@tipos');
    Route::get('coordinadores', 'ajax\UsuarioController@getCoordinadores');

    Route::prefix('paises')->group(function () {
        Route::get('/', 'ajax\PaisesController@index');
		Route::get('{id_pais}/provincias', 'ajax\PaisesController@provincias');
		Route::get('{id_pais}/provincias/{id_provincia}/localidades', 'ajax\PaisesController@localidades');
	});
	Route::prefix('usuario')->group(function(){
		Route::get('', function(){
			if(Auth::check()) {
				return Auth::user();
			}
			return '';
		});
        Route::get('validar/{verbo}', 'ajax\UsuarioController@validar');
        Route::get('perfil', 'ajax\UsuarioController@perfil');
        Route::post('', 'ajax\UsuarioController@create');
        Route::put('', 'ajax\UsuarioController@update');
		Route::get('valid_new_mail', 'ajax\UsuarioController@validar_nuevo_mail');
        Route::put('linkear', 'ajax\UsuarioController@linkear');
        Route::get('inscripciones', 'ajax\UsuarioController@inscripciones');
        Route::delete('inscripciones/{id}', 'ajax\UsuarioController@desinscribir');
    });
    Route::post('actividades/provincias', 'ajax\ActividadesController@filtrarProvinciasYLocalidades');
    Route::post('actividades/tipos', 'ajax\ActividadesController@filtrarTiposDeActividades');
    Route::post('actividades', 'ajax\ActividadesController@index');
    Route::get('actividades/{id}', 'ajax\ActividadesController@show');
});


Route::get('/registro', function (Request $request) {
    $request = request();
    if (url('/registro') != $request->headers->get('referer')) $request->session()->put('login_callback', $request->headers->get('referer'));
    return view('registro');
})->middleware('guest');

Route::get('/actividades/{id}', 'ActividadesController@show');

Route::get('/inscripciones/actividad/{id}', function ($id) {
    $actividad = Actividad::find($id);
    return view('inscripciones.seleccionar_puntos_encuentro')->with('actividad', $actividad);
})->middleware('requiere.auth', 'can:inscribir,App\Actividad,id');

Route::post('/inscripciones/actividad/{id}/confirmar', 'InscripcionesController@confirmar')->middleware('can:inscribir,App\Actividad,id');

Route::post('/inscripciones/actividad/{id}/gracias', 'InscripcionesController@create')->middleware('can:inscribir,App\Actividad,id');
Route::get('/inscripciones/actividad/{id}/inscripto', 'InscripcionesController@inscripto');

Route::get('/perfil', 'PerfilController@show')->middleware('auth');
Route::get('/perfil/actividades', 'PerfilController@actividades')->middleware('auth');

Auth::routes();
Route::get('/auth/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('/auth/{provider}/callback', 'Auth\LoginController@callbackFromProvider');

Route::get('autenticado', function () {
    return (Auth::check()) ? 'si' : 'no';
});

Route::get('/usuario/verificar_mail/{token}', 'Auth\RegisterController@verificar_mail');

Route::prefix('/admin')->group(function () {
    Route::get('/actividades', 'backoffice\ActividadesController@index');
    Route::get('/actividades/{id}', 'backoffice\ActividadesController@show');
    Route::delete('/actividades/{id}', 'backoffice\ActividadesController@destroy');
    Route::get('/actividades/{id}/editar', 'backoffice\ActividadesController@edit');
    Route::post('/actividades/{id}/editar', 'backoffice\ActividadesController@update');
    Route::get('/ajax/actividades', 'backoffice\ajax\ActividadesController@index');
    Route::get('/ajax/unidadesOrganizacionales', 'backoffice\ajax\UnidadOrganizacionalController@index');
});

Route::get('/usuario/verificar_mail/{token}', 'Auth\RegisterController@verificar_mail');