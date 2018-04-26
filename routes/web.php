<?php

use Illuminate\Support\Facades\Auth;

//Frontoffice

Route::get('/', 'HomeController@index')->name('home');

Route::get('/actividades', 'ActividadesController@index');


// Ajax calls
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
//Fin Ajax calls

// Registracion
Route::get('/registro', function (Request $request) {
    $request = request();
    if (url('/registro') != $request->headers->get('referer')) $request->session()->put('login_callback', $request->headers->get('referer'));
    return view('registro');
})->middleware('guest');

Auth::routes();
Route::get('/auth/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('/auth/{provider}/callback', 'Auth\LoginController@callbackFromProvider');

Route::get('autenticado', function () {
    return (Auth::check()) ? 'si' : 'no';
});
Route::get('/usuario/verificar_mail/{token}', 'Auth\RegisterController@verificar_mail');

// Flujo de inscripciones

Route::get('/actividades/{id}', 'ActividadesController@show');

Route::prefix('/inscripciones/actividad/{id}')->middleware('requiere.auth', 'can:inscribir,App\Actividad,id')->group(function (){
    Route::get('', 'InscripcionesController@puntoDeEncuentro');
    Route::post('/confirmar', 'InscripcionesController@confirmar');
    Route::post('/gracias', 'InscripcionesController@create');
    Route::get('/inscripto', 'InscripcionesController@inscripto'); //tendría que ser una ruta por ajax
});

//Fin Flujo de inscripciones


// Perfil y mis inscripciones
Route::prefix('/perfil')->middleware('auth')->group(function (){
    Route::get('/', 'PerfilController@show');
    Route::get('/actividades', 'PerfilController@actividades');
});
//Fin Frontoffice

//Backoffice

Route::prefix('/admin')->middleware(['auth', 'can:accesoBackoffice'])->group(function () {
    Route::get('/roles', 'backoffice\RolesController@index');
    Route::get('/actividades', 'backoffice\ActividadesController@index');
    Route::get('/actividades/crear', 'backoffice\ActividadesController@create');
    Route::post('/actividades/crear', 'backoffice\ActividadesController@store');
    Route::get('/actividades/usuario', 'backoffice\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades/{id}', 'backoffice\ActividadesController@show');
    Route::delete('/actividades/{id}', 'backoffice\ActividadesController@destroy')->middleware('can:borrar,App\Actividad,id');
    Route::get('/actividades/{id}/editar', 'backoffice\ActividadesController@edit')->middleware('can:editar,App\Actividad,id');
    Route::post('/actividades/{id}/editar', 'backoffice\ActividadesController@update')->middleware('can:editar,App\Actividad,id');
    Route::get('/ajax/actividades', 'backoffice\ajax\ActividadesController@index');
    Route::get('/ajax/oficinas', 'backoffice\ajax\OficinasController@index');
    Route::get('/ajax/usuarios', 'backoffice\ajax\UsuariosController@index');
    Route::get('/ajax/roles', 'backoffice\ajax\RolesController@index');
    Route::get('/ajax/actividades/usuario', 'backoffice\ajax\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/ajax/unidadesOrganizacionales', 'backoffice\ajax\UnidadOrganizacionalController@index');
});

