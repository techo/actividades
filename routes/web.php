<?php

use Illuminate\Support\Facades\Auth;

//Frontoffice
Route::get('/', 'HomeController@index')->name('home');
Route::get('/login', 'HomeController@index')->name('home');
Route::get('/actividades', 'ActividadesController@index');
Route::get('/cookie/close', function(){
    return response()->json([],200)->cookie('cookie-policy-accepted', 'ok', 60*24*365);
});
Route::get('/carta-voluntariado', function (){
    return view('terminos.actividades.show');
});
Route::get('/desuscribirse/{uuid}', 'UnsubscribeController@view');
Route::post('/desuscribirse/{uuid}', 'UnsubscribeController@confirm')->name('unsubscribe.confirmar');

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

	Route::prefix('usuario')->group(
	    function(){
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
        Route::delete('', 'ajax\UsuarioController@delete'); //Anonimiza cuenta de usuario
		Route::get('valid_new_mail', 'ajax\UsuarioController@validar_nuevo_mail'); //TODO revisar si se está usando
        Route::put('linkear', 'ajax\UsuarioController@linkear');
        Route::get('inscripciones', 'ajax\UsuarioController@inscripciones');
        Route::delete('inscripciones/{id}', 'ajax\UsuarioController@desinscribir');
    });
    
    Route::post('actividades/provincias', 'ajax\ActividadesController@filtrarProvinciasYLocalidades');
    Route::post('actividades/tipos', 'ajax\ActividadesController@filtrarTiposDeActividades');
    Route::post('actividades', 'ajax\ActividadesController@index');
    Route::get('actividades/{id}', 'ajax\ActividadesController@show');

    Route::get('auditorias/{tabla}/{id}', 'ajax\AuditoriasController@show');
});
//Fin Ajax calls

// Registracion
Route::get('/registro', function (Request $request) {
    $request = request();
    if (url('/registro') != $request->headers->get('referer')) $request->session()->put('login_callback', $request->headers->get('referer'));
    return view('registro');
})->middleware('guest');

Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('/auth/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('/auth/{provider}/callback', 'Auth\LoginController@callbackFromProvider');

Route::get('autenticado', function () {
    return (Auth::check()) ? 'si' : 'no';
});
Route::get('/usuario/verificar_mail/{token}', 'Auth\RegisterController@verificar_mail');

// Evaluaciones
Route::get('/actividades/{id}/evaluaciones', 'EvaluacionesController@index')->middleware('requiere.auth', 'can:evaluar,App\Actividad,id');
Route::post('/actividades/{id}/evaluaciones', 'EvaluacionesController@evaluarActividad')->middleware('requiere.auth', 'can:evaluar,App\Actividad,id');
Route::post('/actividades/{id}/persona/{idPersona}/evaluar', 'EvaluacionesController@evaluarPersona')->middleware('requiere.auth', 'can:evaluar,App\Actividad,id');
Route::get('/admin/actividades/{id}/exportar-evaluaciones-voluntarios', 'backoffice\ReportController@exportarEvaluacionesPersonas')->middleware('requiere.auth');
Route::get('/admin/actividades/{id}/exportar-evaluaciones', 'backoffice\ReportController@exportarEvaluacionesActividad')->middleware('requiere.auth');

// Flujo de inscripciones

Route::get('/actividades/{id}', 'ActividadesController@show');

Route::prefix('/inscripciones/actividad/{id}')->middleware('requiere.auth', 'can:confirmar,App\Actividad,id')->group(function (){
    Route::get('/confirmar/donacion','InscripcionesController@confirmarDonacion');
    Route::post('/confirmar/donacion/checkout','InscripcionesController@donacionCheckout');
    Route::post('/confirmar', 'InscripcionesController@confirmar');
});

Route::prefix('/inscripciones/actividad/{id}')->middleware('requiere.auth', 'can:inscribir,App\Actividad,id')->group(function (){
    Route::get('', 'InscripcionesController@puntoDeEncuentro');
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
//TODO: Agrupar rutas
Route::get('admin/ajax/search/usuarios', 'backoffice\ajax\UsuariosController@usuariosSearch'); //TODO: hack, mejorar

Route::prefix('/admin')->middleware(['auth', 'can:accesoBackoffice'])->group(function () {
    Route::get('/usuarios', 'backoffice\UsuariosController@index')->middleware('permission:ver_usuarios');
    Route::get('/usuarios/registrar', 'backoffice\UsuariosController@create')->middleware('permission:ver_usuarios');
    Route::post('/usuarios/registrar', 'backoffice\ajax\UsuariosController@store')->middleware('permission:ver_usuarios');
    Route::get('/usuarios/{id}', 'backoffice\UsuariosController@show')->middleware('permission:ver_usuarios');
    Route::post('/usuarios/{id}/editar', 'backoffice\ajax\UsuariosController@update')->middleware('permission:editar_usuarios') ;

    //panel de usuario
    Route::get('/ajax/usuarios/{id}/inscripciones', 'backoffice\ajax\UsuariosController@inscripciones')
        ->middleware('permission:editar_usuarios');
    Route::get('/usuarios/{id}/exportar-inscripciones', 'backoffice\ReportController@exportarInscripcionesUsuario')
        ->middleware('permission:editar_usuarios');
    Route::get('/ajax/usuarios/{id}/inscripciones-stats', 'backoffice\ajax\EvaluacionesController@getStatsPorUsuario')
        ->middleware('permission:editar_usuarios');
    Route::get('/ajax/usuarios/{id}/evaluaciones', 'backoffice\ajax\UsuariosController@evaluaciones')
        ->middleware('permission:editar_usuarios');
    Route::get('/usuarios/{id}/exportar-evaluaciones', 'backoffice\ReportController@exportarEvaluacionesUsuario')
        ->middleware('permission:editar_usuarios');

    Route::get('/roles', 'backoffice\UsuariosRolesController@index')->middleware('permission:asignar_roles'); //TODO: Mejorar la nomenclatura de la ruta
    Route::get('/ajax/roles', 'backoffice\ajax\UsuariosRolesController@index')->middleware('permission:asignar_roles'); //TODO: Mejorar la nomenclatura de la ruta
    Route::post('/roles/usuario/{id}', 'backoffice\UsuariosRolesController@update')->middleware('permission:asignar_roles');
    Route::get('/actividades', 'backoffice\ActividadesController@index')->middleware('role:admin');
    Route::get('/actividades/crear', 'backoffice\ActividadesController@create');
    Route::post('/actividades/crear', 'backoffice\ActividadesController@store');
    Route::get('/actividades/usuario', 'backoffice\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades/usuario/exportar', 'backoffice\ReportController@exportarMisActividades')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades/exportar', 'backoffice\ReportController@exportarActividades');
    Route::get('/actividades/{id}', 'backoffice\ActividadesController@show');
    Route::delete('/actividades/{id}', 'backoffice\ActividadesController@destroy')->middleware('can:borrar,App\Actividad,id');
    Route::get('/actividades/{id}/editar', 'backoffice\ActividadesController@edit')->middleware('can:editar,App\Actividad,id');
    Route::post('/actividades/{id}/editar', 'backoffice\ActividadesController@update')->middleware('can:editar,App\Actividad,id');
    Route::get('/actividades/{id}/inscripciones/exportar', 'backoffice\ReportController@exportarInscripciones')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/ajax/actividades/{id}/inscripciones', 'backoffice\ajax\InscripcionesController@index')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones', 'backoffice\ajax\InscripcionesController@store')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::get('/ajax/actividades/{id}/grupos/getInscriptos', 'backoffice\ajax\InscripcionesController@getInscriptos')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/ajax/actividades/{id}/puntos', 'backoffice\ajax\ActividadesController@getPuntos')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/enviar-evaluaciones', 'backoffice\ajax\EvaluacionesController@enviar')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/stats', 'backoffice\ajax\EvaluacionesController@getActividadStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/chartdata', 'backoffice\ajax\EvaluacionesController@getActividadChartData')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/general/stats', 'backoffice\ajax\EvaluacionesController@getGeneralStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/voluntarios/stats', 'backoffice\ajax\EvaluacionesController@getVoluntariosStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/voluntarios/chartdata', 'backoffice\ajax\EvaluacionesController@getVoluntariosChartData')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::post('/ajax/actividades/{id}/grupos/cambiar/grupo', 'backoffice\ajax\GruposActividadesController@update');
    Route::post('/ajax/actividades/{id}/grupos/cambiar/rol', 'backoffice\ajax\GruposActividadesController@updateRol');
    Route::post('/ajax/actividades/{id}/grupos/borrar', 'backoffice\ajax\GruposActividadesController@delete');
    Route::get('/ajax/actividades/{id}/grupos', 'backoffice\ajax\ActividadesController@grupos');
    Route::post('/ajax/actividades/{id}/clonar', 'backoffice\ActividadesController@clonar');

    Route::post('/ajax/actividades/{id}/inscripciones/desinscribir',
    'backoffice\ajax\InscripcionesController@desinscribir')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/rol', 'backoffice\ajax\InscripcionesController@asignarRol')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/grupo', 'backoffice\ajax\InscripcionesController@asignarGrupo')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/punto', 'backoffice\ajax\InscripcionesController@asignarPunto')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/estado', 'backoffice\ajax\InscripcionesController@cambiarEstado')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/asistencia', 'backoffice\ajax\InscripcionesController@cambiarAsistencia')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/{inscripcion}', 'backoffice\ajax\InscripcionesController@update')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/{inscripcion}/eliminar', 'backoffice\ajax\InscripcionesController@destroy')->middleware('can:verInscripciones,App\Inscripcion,id');
    

    Route::post('/ajax/actividades/{id}/inscripciones/procesar/archivo', 'backoffice\ajax\InscripcionesController@procesarArchivo')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/inscripciones/importar/template', 'backoffice\InscripcionesController@descargarTemplate'); //TODO: segurizar
    Route::get('/ajax/actividades', 'backoffice\ajax\ActividadesController@index');
    Route::get('/ajax/actividades/usuario', 'backoffice\ajax\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/ajax/oficinas', 'backoffice\ajax\OficinasController@index');
    Route::get('/ajax/usuarios', 'backoffice\ajax\UsuariosController@index')->middleware('permission:ver_usuarios');
    Route::get('/ajax/usuarios/{id}/rol','backoffice\ajax\UsuariosController@getRol')->middleware('permission:asignar_roles'); //TODO: Mejorar la nomenclatura de la ruta
    Route::post('/ajax/grupos/{id}/miembros', 'backoffice\ajax\GruposController@index');// para testing only. eliminar
    Route::get('/ajax/grupos/{id}/miembros', 'backoffice\ajax\GruposController@index');
    Route::post('/ajax/grupos', 'backoffice\ajax\GruposController@store');
    Route::post('/ajax/grupos/{idGrupo}/inscriptos', 'backoffice\ajax\GruposController@incluirInscripto');
    Route::get('/ajax/personas/{id}', 'ajax\PersonasController@show');
    Route::get('/logs/{proceso}', 'backoffice\LogsController@show')->name('logs'); //TODO: segurizar

    Route::get('/estadisticas', 'backoffice\EstadisticasController@index');
    Route::get('/estadisticas/actividades', 'backoffice\EstadisticasController@actividades');
    Route::get('/estadisticas/personas', 'backoffice\EstadisticasController@personas');

});

Route::prefix('/pagos/')->group(function() {
    Route::get('{idInscripcion}/response', 'PagosController@response');
    Route::post('{idInscripcion}/confirmation', 'PagosController@confirmation');
});
