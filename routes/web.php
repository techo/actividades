<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

//Frontoffice
#Route::get('/', 'HomeController@index')->name('home');



Route::get('/cookie/close', function(){
    return response()->json([],200)->cookie('cookie-policy-accepted', 'ok', 60*24*365);
});
Route::get('/carta-voluntariado', function (){ return view('terminos.actividades.show');  });
Route::get('/carta-voluntariado-brasil', function (){ return view('terminos.actividades.show_brasil');  });
Route::get('/desuscribirse/{uuid}', 'UnsubscribeController@view');
Route::post('/desuscribirse/{uuid}', 'UnsubscribeController@confirm')->name('unsubscribe.confirmar');

Route::get('/seleccionar-pais/{codigo}', 'HomeController@seleccionarPais');
Route::get('/deseleccionar-pais', 'HomeController@deseleccionarPais');

// Ajax calls
Route::prefix('ajax')->group(function () {
    Route::get('provincias/{id}', 'ajax\ProvinciasController@show');
    Route::get('categorias', 'ajax\CategoriasController@index');
    Route::get('categorias/{id}', 'ajax\CategoriasController@show');
    Route::get('categorias/{id}/tipos', 'ajax\CategoriasController@tipos');
    Route::get('coordinadores', 'ajax\UsuarioController@getCoordinadores');
    Route::get('personas', 'ajax\UsuarioController@getPersonas');


    Route::prefix('paises')->group(function () {
        Route::get('/', 'ajax\PaisesController@index');
		Route::get('{id_pais}/provincias', 'ajax\PaisesController@provincias');
		Route::get('{id_pais}/provincias/{id_provincia}/localidades', 'ajax\PaisesController@localidades');
        Route::get('/habilitados', 'ajax\PaisesController@paisesHabilitados');
        Route::get('/propios', 'ajax\PaisesController@paisesPropios');
        Route::get('/conInstitucionesEducativas', 'ajax\PaisesController@paisesConInstitucionesEducativas');
	});
    Route::middleware(['requiere.auth'])->group(function () {
        Route::prefix('institucionEducativa')->group(function() {
            Route::get('', 'ajax\InstitucionEducativaController@index');
            Route::get('{idInstitucionEducativa}', 'ajax\InstitucionEducativaController@get');
            Route::get('pais/{idPais}', 'ajax\InstitucionEducativaController@porPais');
        });
        
        Route::post('fichaMedica', 'ajax\FichaMedicaController@upsert');
        Route::post('fichaMedica/archivo_medico', 'ajax\FichaMedicaController@uploadArchivoMedico');

        Route::prefix('estudios')->group(function () {
            Route::post('', 'ajax\EstudiosController@create');
            Route::put('', 'ajax\EstudiosController@update');
            Route::delete('/{id}', 'ajax\EstudiosController@delete');
        });

        Route::prefix('equipos')->group(function () {
            Route::put('', 'ajax\EquiposController@update');
            Route::post('carta_compromiso', 'ajax\EquiposController@updateCartaCompromiso');
        });
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
        Route::post('perfil/cambiar_photo', 'ajax\UsuarioController@cambiar_photo');
        Route::post('', 'ajax\UsuarioController@create');
        Route::put('', 'ajax\UsuarioController@update');
        Route::delete('', 'ajax\UsuarioController@delete'); //Anonimiza cuenta de usuario
		Route::get('valid_new_mail', 'ajax\UsuarioController@validar_nuevo_mail'); //TODO revisar si se está usando
        Route::put('linkear', 'ajax\UsuarioController@linkear');
        Route::get('inscripciones', 'ajax\UsuarioController@inscripciones');
        Route::delete('inscripciones/{id}', 'ajax\UsuarioController@desinscribir');
    });
    
    Route::get('actividades/provincias', 'ajax\ActividadesController@filtrarProvinciasYLocalidades');
    Route::get('actividades/tipos', 'ajax\ActividadesController@filtrarTiposDeActividades');
    Route::get('actividades', 'ajax\ActividadesController@index');
    Route::post('actividades/suscribe', 'ajax\ActividadesController@suscribe');
    Route::get('actividades/{id}', 'ajax\ActividadesController@show');

    Route::get('auditorias/{tabla}/{id}', 'ajax\AuditoriasController@show');

    Route::get('estadisticas/voluntades_movilizadas', 'EstadisticasController@voluntades_movilizadas');
    Route::get('estadisticas/actividades', 'EstadisticasController@actividades');
    Route::get('estadisticas/personas_movilizadas', 'EstadisticasController@personas_movilizadas');

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
//Route::get('/usuario/verificar_mail/{token}', 'Auth\RegisterController@verificar_mail');

// Evaluaciones
Route::get('/actividades/{id}/evaluaciones', 'EvaluacionesController@index')->middleware('requiere.auth', 'can:evaluar,App\Actividad,id');
Route::post('/actividades/{id}/evaluaciones', 'EvaluacionesController@evaluarActividad')->middleware('requiere.auth', 'can:evaluar,App\Actividad,id');
Route::post('/actividades/{id}/persona/{idPersona}/evaluar', 'EvaluacionesController@evaluarPersona')->middleware('requiere.auth', 'can:evaluar,App\Actividad,id');

// Flujo de inscripciones

Route::get('/actividades/{id}', 'ActividadesController@show')->middleware('pais');

Route::prefix('/inscripciones/actividad/{id}')->middleware('requiere.auth', 'can:confirmar,App\Actividad,id')->group(function (){
    Route::get('/confirmar/donacion','InscripcionesController@confirmarDonacion');
    Route::post('/confirmar/donacion/checkout','InscripcionesController@donacionCheckout');
    
    Route::post('/confirmar', 'InscripcionesController@confirmar');
});

Route::post('/ajax/inscripcion/voucherPago','InscripcionesController@voucherPago');

Route::get('/inscripciones/actividad/{id}', 'InscripcionesController@puntoDeEncuentro');
Route::get('/inscripciones/actividad/{id}/inscripto', 'InscripcionesController@inscripto'); //tendría que ser una ruta por ajax
Route::post('/inscripciones/actividad/{id}/gracias', 'InscripcionesController@create')->middleware('requiere.auth', 'can:inscribir,App\Actividad,id');

//Fin Flujo de inscripciones

//Verificacion de email
Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

// Perfil y mis inscripciones
Route::prefix('/perfil')->middleware('verified', 'auth')->group(function (){
    Route::get('/', 'PerfilController@show')->middleware('verified');
    Route::get('/actividades', 'PerfilController@actividades');
    Route::get('/evaluacion', 'PerfilController@evaluacion');
    Route::get('/cambiar_email', 'PerfilController@cambiar_email');
    Route::post('/actualizar_email', 'PerfilController@actualizar_email');
    Route::get('/constancia_voluntariado', 'PerfilController@get_constancia_voluntariado');
});


//Fin Frontoffice

//Backoffice
//TODO: Agrupar rutas
Route::get('admin/ajax/search/usuarios', 'backoffice\ajax\UsuariosController@usuariosSearch'); //TODO: hack, mejorar

Route::prefix('/admin')->middleware(['verified', 'auth', 'can:accesoBackoffice'])->group(function () {

    Route::get('/novedades', function(){
        $n = \App\Novedad::latest('created_at')->first();
        return response()->json($n,200);
    });

    Route::get('/novedades/visto', function(){
        $n = \App\Novedad::latest('created_at')->first();
        if($n)
            return response()->json([$n->id],200)->cookie('cookie-novedades', $n->id, 10080);
        
        return response()->json(['no hay novedades'],200);
    });

    Route::get('/usuarios', 'backoffice\UsuariosController@index')->middleware('role:admin');
    Route::get('/suscriptos', 'backoffice\UsuariosController@suscriptos')->middleware('role:admin');
    Route::get('/usuarios/registrar', 'backoffice\UsuariosController@create')->middleware('role:admin');
    Route::post('/usuarios/registrar', 'backoffice\ajax\UsuariosController@store')->middleware('role:admin');
    Route::get('/usuarios/{id}', 'backoffice\UsuariosController@show')->middleware('permission:ver_usuarios');
    Route::post('/usuarios/{id}/editar', 'backoffice\ajax\UsuariosController@update')->middleware('role:admin');
    Route::delete('/usuarios/{id}', 'backoffice\UsuariosController@delete')->middleware('permission:borrar_usuarios');
    Route::post('/usuarios/{persona}/fusionar', 'backoffice\ajax\UsuariosController@fusionar')->middleware('role:admin');


    // panel Equipos

    Route::prefix('/equipos')->middleware(['role:admin|coordinador'])->group(function() {
        Route::get('', 'backoffice\EquiposController@index');
        Route::get('/crear', 'backoffice\EquiposController@create');
        Route::post('/registrar', 'backoffice\EquiposController@store');
        Route::get('/{idEquipo}', 'backoffice\EquiposController@show');
        Route::put('/{idEquipo}', 'backoffice\EquiposController@update');
        Route::delete('/{idEquipo}', 'backoffice\EquiposController@destroy');
        Route::prefix('/{idEquipo}/integrantes')->group(function() {
            Route::get('', 'backoffice\IntegrantesController@index');
        });

        Route::prefix('/{idEquipo}/coordinacion')->group(function() {
            Route::get('', 'backoffice\CoordinadorEquipoController@index');
        });

    });
    Route::prefix('ajax/equipos')->middleware(['role:admin|coordinador'])->group(function() {
        Route::get('', 'backoffice\ajax\EquiposController@index');
        
        Route::prefix('/{idEquipo}/integrante')->group(function() {
            Route::get('', 'backoffice\ajax\IntegrantesController@index'); 
            Route::post('/crear', 'backoffice\ajax\IntegrantesController@store');  
            Route::put('/{idIntegrante}', 'backoffice\ajax\IntegrantesController@update');
            Route::delete('/{idIntegrante}', 'backoffice\ajax\IntegrantesController@delete');
            Route::get('/{idIntegrante}', 'backoffice\ajax\IntegrantesController@get');  
            Route::post('/{idIntegrante}/archivos', 'backoffice\ajax\IntegrantesController@uploadArchivos');  
        });

        Route::prefix('/{idEquipo}/coordinacion')->group(function() {
            Route::get('', 'backoffice\ajax\CoordinadorEquipoController@index');
            Route::post('/{idPersona}', 'backoffice\ajax\CoordinadorEquipoController@store');
            Route::delete('/{idCoordinador}', 'backoffice\ajax\CoordinadorEquipoController@delete');
        });
    });


    
    
    //panel de usuario
    Route::get('/ajax/usuarios/{id}/inscripciones', 'backoffice\ajax\UsuariosController@inscripciones')
        ->middleware('permission:ver_usuarios');
    Route::get('/usuarios/{id}/exportar-inscripciones', 'backoffice\ReportController@exportarInscripcionesUsuario')
        ->middleware('permission:ver_usuarios');
    Route::get('/ajax/usuarios/{id}/inscripciones-stats', 'backoffice\ajax\EvaluacionesController@getStatsPorUsuario')
        ->middleware('permission:ver_usuarios');
    Route::get('/ajax/usuarios/{id}/evaluaciones', 'backoffice\ajax\UsuariosController@evaluaciones')
        ->middleware('role:admin');
    Route::get('/usuarios/{id}/exportar-evaluaciones', 'backoffice\ReportController@exportarEvaluacionesUsuario')
        ->middleware('role:admin');
    Route::get('/ajax/usuarios/{id}/estudios', 'backoffice\ajax\UsuariosController@estudios')
        ->middleware('permission:ver_usuarios');

    Route::get('/roles', 'backoffice\UsuariosRolesController@index')->middleware('permission:asignar_roles'); //TODO: Mejorar la nomenclatura de la ruta
    Route::get('/ajax/roles', 'backoffice\ajax\UsuariosRolesController@index')->middleware('permission:ver_usuarios'); //TODO: Mejorar la nomenclatura de la ruta
    Route::post('/roles/usuario/{id}', 'backoffice\UsuariosRolesController@update')->middleware('permission:asignar_roles');

    Route::get('/ajax/actividades/usuario', 'backoffice\ajax\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades', 'backoffice\ActividadesController@index')->middleware('role:admin');
    Route::get('/actividades/crear', 'backoffice\ActividadesController@create');
    Route::post('/actividades/crear', 'backoffice\ActividadesController@store');
    Route::post('/ajax/actividades/{actividad}', 'backoffice\ActividadesController@update');
    Route::get('/actividades/usuario', 'backoffice\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades/usuario/exportar', 'backoffice\ReportController@exportarMisActividades')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades/exportar', 'backoffice\ReportController@exportarActividades')->middleware('role:admin');
    Route::get('/suscriptos/exportar', 'backoffice\ReportController@exportarSuscriptos')->middleware('role:admin');
    
    Route::get('/actividades/{id}/exportar-evaluaciones-voluntarios', 'backoffice\ReportController@exportarEvaluacionesPersonas');
    Route::get('/actividades/{id}/exportar-evaluaciones', 'backoffice\ReportController@exportarEvaluacionesActividad');
    //vista de actividad
    Route::get('/actividades/{id}', 'backoffice\ActividadesController@show')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/puntos', 'backoffice\ActividadesController@puntos')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/inscripciones', 'backoffice\ActividadesController@inscripciones')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{actividad}/inscripcion/{inscripcion}/persona/{persona}', 'backoffice\ActividadesController@confirmarInscripcion')->middleware('can:ver,App\Actividad,actividad');
    Route::get('/actividades/{id}/grupos', 'backoffice\ActividadesController@grupos')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/evaluaciones', 'backoffice\ActividadesController@evaluaciones')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/accesos', 'backoffice\ActividadesController@accesos')->middleware('can:ver,App\Actividad,id');
    
    Route::get('/actividades/{id}/jornadas', 'backoffice\ActividadesController@jornadas')->middleware('can:ver,App\Actividad,id');
    Route::get('/ajax/actividades/{id}/jornadas', 'backoffice\ajax\ActividadesController@jornadas');

    Route::post('ajax/actividades/{id}/jornadas', 'backoffice\ajax\JornadasController@store')->middleware('can:ver,App\Actividad,id');;


    Route::put('/ajax/actividades/{id}/jornadas/{jornada}', 'backoffice\ajax\JornadasController@update');
    Route::delete('/ajax/actividades/{id}/jornadas/{jornada}', 'backoffice\ajax\JornadasController@delete');
    
    Route::get('/ajax/actividades/{id}', 'backoffice\ActividadesController@actividad');
    Route::get('/ajax/actividades/{id}/accesos', 'backoffice\ActividadesController@coordinadores');
    Route::post('/ajax/actividades/{actividad}/accesos/{persona}', 'backoffice\ActividadesController@guardarCoordinador')->middleware('can:ver,App\Actividad,actividad');
    Route::post('/ajax/actividades/{actividad}/accesos/{coordinador}/borrar', 'backoffice\ActividadesController@eliminarCoordinador')->middleware('can:ver,App\Actividad,actividad');
    Route::post('/ajax/actividades/{actividad}/accesos/{coordinador}/activaWhatsapp', 'backoffice\ajax\ActividadesController@activaWhatsappAccesos')->middleware('can:ver,App\Actividad,actividad');
    Route::delete('/actividades/{id}', 'backoffice\ActividadesController@destroy')->middleware('can:borrar,App\Actividad,id');
    Route::get('/actividades/{id}/editar', 'backoffice\ActividadesController@edit')->middleware('can:editar,App\Actividad,id');
    Route::post('/actividades/{id}/editar', 'backoffice\ActividadesController@update')->middleware('can:editar,App\Actividad,id');
    Route::get('/actividades/{id}/inscripciones/exportar', 'backoffice\ReportController@exportarInscripcionesActividad')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/ajax/actividades/{id}/inscripciones', 'backoffice\ajax\InscripcionesController@index')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones', 'backoffice\ajax\InscripcionesController@store')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::get('/ajax/actividades/{id}/grupos/getInscriptos', 'backoffice\ajax\InscripcionesController@getInscriptos')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::get('/ajax/actividades/{id}/puntos', 'backoffice\ajax\ActividadesController@puntos')->middleware('can:editar,App\Actividad,id');

    Route::post('/ajax/actividades/{id}/imagen-tarjeta', 'backoffice\ajax\ActividadesController@storeImagenTarjeta');
    Route::post('/ajax/actividades/{id}/imagen-destacada', 'backoffice\ajax\ActividadesController@storeImagenDestacada');

    Route::get('/ajax/actividades/{id}/puntos/{punto}', 'backoffice\ajax\PuntosController@show');
    Route::post('/ajax/actividades/{id}/puntos', 'backoffice\ajax\PuntosController@store')->middleware('can:editar,App\Actividad,id');
    Route::post('/ajax/actividades/{id}/puntos/{punto}', 'backoffice\ajax\PuntosController@update');
    Route::delete('/ajax/actividades/{id}/puntos/{punto}', 'backoffice\ajax\PuntosController@delete');

    Route::post('/ajax/actividades/{id}/enviar-evaluaciones', 'backoffice\ajax\EvaluacionesController@enviar')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/stats', 'backoffice\ajax\EvaluacionesController@getActividadStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/chartdata', 'backoffice\ajax\EvaluacionesController@getActividadChartData')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/general/stats', 'backoffice\ajax\EvaluacionesController@getGeneralStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/voluntarios/stats', 'backoffice\ajax\EvaluacionesController@getVoluntariosStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/voluntarios/chartdata', 'backoffice\ajax\EvaluacionesController@getVoluntariosChartData')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::post('/ajax/actividades/{id}/grupos/cambiar/grupo', 'backoffice\ajax\GruposActividadesController@update');
    Route::post('/ajax/actividades/{id}/grupos/cambiar/rol', 'backoffice\ajax\GruposActividadesController@updateRol');
    Route::post('/ajax/actividades/{id}/grupos/cambiar/link', 'backoffice\ajax\GruposActividadesController@updateLink');
    Route::post('/ajax/actividades/{id}/grupos/borrar', 'backoffice\ajax\GruposActividadesController@delete');
    Route::get('/ajax/actividades/{id}/grupos', 'backoffice\ajax\ActividadesController@grupos');
    Route::post('/ajax/actividades/{id}/clonar', 'backoffice\ActividadesController@clonar');

    Route::post('/ajax/actividades/{id}/inscripciones/desinscribir',
    'backoffice\ajax\InscripcionesController@desinscribir')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/rol', 'backoffice\ajax\InscripcionesController@asignarRol')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/grupo', 'backoffice\ajax\InscripcionesController@asignarGrupo')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/punto', 'backoffice\ajax\InscripcionesController@asignarPunto')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/confirmacion', 'backoffice\ajax\InscripcionesController@cambiarConfirmacion')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/pago', 'backoffice\ajax\InscripcionesController@cambiarPago')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/asistencia', 'backoffice\ajax\InscripcionesController@cambiarAsistencia')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/{inscripcion}', 'backoffice\ajax\InscripcionesController@update')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/{inscripcion}/eliminar', 'backoffice\ajax\InscripcionesController@destroy')->middleware('can:verInscripciones,App\Inscripcion,id');
    

    Route::post('/ajax/actividades/{id}/inscripciones/procesar/archivo', 'backoffice\ajax\InscripcionesController@procesarArchivo')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/inscripciones/importar/template', 'backoffice\InscripcionesController@descargarTemplate'); //TODO: segurizar
    Route::get('/ajax/actividades', 'backoffice\ajax\ActividadesController@index');

    Route::get('/ajax/actividades/usuario', 'backoffice\ajax\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/ajax/oficinas', 'backoffice\ajax\OficinasController@getOficinas');
    Route::get('/ajax/oficinas/pais/{idpais}', 'backoffice\ajax\OficinasController@getOficinasPais');
    Route::get('/ajax/configuracion/oficinas', 'backoffice\ajax\OficinasController@index');

    Route::get('/ajax/usuarios', 'backoffice\ajax\UsuariosController@index')->middleware('permission:ver_usuarios');

    Route::get('/ajax/suscriptos', 'backoffice\ajax\SuscriptosController@index')->middleware('permission:ver_usuarios');
    Route::get('/ajax/usuarios/{id}/rol','backoffice\ajax\UsuariosController@getRol')->middleware('permission:asignar_roles'); //TODO: Mejorar la nomenclatura de la ruta
    Route::post('/ajax/grupos/{id}/miembros', 'backoffice\ajax\GruposController@index');// para testing only. eliminar
    Route::get('/ajax/grupos/{id}/miembros', 'backoffice\ajax\GruposController@index');
    Route::post('/ajax/grupos', 'backoffice\ajax\GruposController@store');
    Route::post('/ajax/grupos/{idGrupo}/inscriptos', 'backoffice\ajax\GruposController@incluirInscripto');
    Route::get('/ajax/personas/{id}', 'ajax\PersonasController@show');
    Route::get('/logs/{proceso}', 'backoffice\LogsController@show')->name('logs'); //TODO: segurizar

    Route::get('/estadisticas', 'backoffice\EstadisticasController@index');
    Route::get('/estadisticas/actividades', function () { return view('backoffice.estadisticas.actividades'); });
    Route::get('/estadisticas/personas', function () { return view('backoffice.estadisticas.personas'); });
    Route::get('/estadisticas/coordinadores', function () { return view('backoffice.estadisticas.coordinadores'); });

    Route::get('/ajax/paises', 'ajax\PaisesController@paises');
    Route::get('/ajax/estadisticas/grafico-inscripciones', 'backoffice\EstadisticasController@grafico_inscripciones');
    Route::get('/ajax/estadisticas/grafico-actividades', 'backoffice\EstadisticasController@grafico_actividades');
    Route::get('/ajax/estadisticas/grafico-evaluaciones', 'backoffice\EstadisticasController@grafico_evaluaciones');
    Route::get('/ajax/estadisticas/inscripciones-por-actividad', 'backoffice\EstadisticasController@inscripciones_por_actividad');
    Route::get('/ajax/estadisticas/evaluaciones-por-actividad', 'backoffice\EstadisticasController@evaluaciones_por_actividad');
    Route::get('/ajax/estadisticas/coordinadores', 'backoffice\EstadisticasController@coordinadores');
    Route::get('/ajax/estadisticas/inscripciones', 'backoffice\EstadisticasController@inscripciones');
    Route::get('/ajax/estadisticas/evaluaciones-sociales', 'backoffice\EstadisticasController@evaluaciones_sociales');
    Route::get('/ajax/estadisticas/evaluaciones-tecnicas', 'backoffice\EstadisticasController@evaluaciones_tecnicas');

    Route::get('/ajax/estadisticas/inscripciones/exportar', 'backoffice\ReportController@ExportarInscripciones')->middleware('role:admin');;
    Route::get('/ajax/estadisticas/inscripciones/personas/exportar', 'backoffice\ReportController@exportarPersonasInscriptas')->middleware('role:admin');;
    Route::get('/ajax/estadisticas/evaluaciones/exportar', 'backoffice\ReportController@exportarEvaluacionesGenerales')->middleware('role:admin');;
    Route::get('/ajax/estadisticas/evaluadores/exportar', 'backoffice\ReportController@exportarEvaluadoresGenerales')->middleware('role:admin');;

    Route::get('/configuracion/oficinas', 'backoffice\OficinasController@index')->middleware('role:admin');
    Route::get('/configuracion/oficinas/registrar', 'backoffice\OficinasController@create')->middleware('role:admin');
    Route::post('/configuracion/oficinas/registrar', 'backoffice\ajax\OficinasController@store')->middleware('role:admin');
    Route::get('/configuracion/oficinas/{id}', 'backoffice\OficinasController@show')->middleware('role:admin');
    Route::post('/configuracion/oficinas/{id}/editar', 'backoffice\ajax\OficinasController@update')->middleware('role:admin');
    Route::get('/ajax/configuracion/oficinas/{id}', 'backoffice\ajax\OficinasController@get')->middleware('role:admin');
    Route::delete('/configuracion/oficinas/{id}', 'backoffice\ajax\OficinasController@delete')->middleware('role:admin');

    Route::get('/ajax/configuracion/tipos-actividad', 'backoffice\ajax\TiposActividadController@index');
    Route::post('/ajax/configuracion/tipos-actividad/{id}/editar', 'backoffice\ajax\TiposActividadController@update')->middleware('role:admin');
    Route::get('/ajax/configuracion/tipos-actividad/{id}', 'backoffice\ajax\TiposActividadController@get')->middleware('role:admin');
    Route::delete('/ajax/configuracion/tipos-actividad/{id}', 'backoffice\ajax\TiposActividadController@delete')->middleware('role:admin');
    Route::post('/ajax/configuracion/tipos-actividad/registrar', 'backoffice\ajax\TiposActividadController@store')->middleware('role:admin');
    Route::get('/configuracion/tipos-actividad', 'backoffice\TiposActividadController@index')->middleware('role:admin');
    Route::get('/configuracion/tipos-actividad/registrar', 'backoffice\TiposActividadController@create')->middleware('role:admin');
    Route::get('/configuracion/tipos-actividad/{id}', 'backoffice\TiposActividadController@show')->middleware('role:admin');

    Route::get('/configuracion/home-header', 'backoffice\HomeHeaderController@show')->middleware('role:admin');
    Route::post('/ajax/configuracion/home-header/registrar', 'backoffice\HomeHeaderController@store')->middleware('role:admin');
    Route::post('/ajax/configuracion/home-header/{id}/editar', 'backoffice\HomeHeaderController@update')->middleware('role:admin');


        // panel Equipos

    Route::prefix('configuracion/provincias')->middleware(['role:admin'])->group(function() {
        Route::get('', 'backoffice\ProvinciasController@index');
        Route::get('/crear', 'backoffice\ProvinciasController@create');
        Route::post('/registrar', 'backoffice\ProvinciasController@store');
        Route::get('/{idProvincia}', 'backoffice\ProvinciasController@show');
        Route::put('/{idProvincia}', 'backoffice\ProvinciasController@update');
        Route::delete('/{idProvincia}', 'backoffice\ProvinciasController@destroy');
        Route::prefix('/{idProvincia}/localidades')->group(function() {
            Route::get('', 'backoffice\LocalidadesController@index');
        });
    });
    Route::prefix('ajax/configuracion/provincias')->middleware(['role:admin'])->group(function() {
        Route::get('', 'backoffice\ajax\ProvinciasController@index');
        
        Route::prefix('/{idProvincia}/localidades')->group(function() {
            Route::get('', 'backoffice\ajax\LocalidadesController@index'); 
            Route::post('/crear', 'backoffice\ajax\LocalidadesController@store');  
            Route::put('/{idIntegrante}', 'backoffice\ajax\LocalidadesController@update');
            Route::delete('/{idIntegrante}', 'backoffice\ajax\LocalidadesController@delete');
            Route::get('/{idIntegrante}', 'backoffice\ajax\LocalidadesController@get');  
        });
    });

     Route::prefix('configuracion/institucionEducativa')->middleware(['role:admin'])->group(function() {
        Route::get('', 'backoffice\InstitucionEducativaController@index');
        Route::get('/crear', 'backoffice\InstitucionEducativaController@create');
        Route::post('/registrar', 'backoffice\InstitucionEducativaController@store');
        Route::get('/{idInstitucionEducativa}', 'backoffice\InstitucionEducativaController@show');
        Route::put('/{idInstitucionEducativa}', 'backoffice\InstitucionEducativaController@update');
        Route::delete('/{idInstitucionEducativa}', 'backoffice\InstitucionEducativaController@destroy');
    });
    Route::prefix('ajax/configuracion/institucionEducativa')->middleware(['role:admin'])->group(function() {
        Route::get('', 'backoffice\ajax\InstitucionEducativaController@index');
    });
    

});

Route::prefix('/pagos/')->group(function() {
    Route::get('{idInscripcion}/response', 'PagosController@response');
    Route::post('{idInscripcion}/confirmation', 'PagosController@confirmation');
});

Route::get('locale/{locale}', function($locale){
    Session::put('locale',$locale);
    return redirect()->back();
});


    // Route::get('/home', 'HomeController@home');
    Route::get('/', 'HomeController@multiPais');
    // Route::get('/', 'ActividadesController@index');
    // Route::get('/actividades', 'ActividadesController@index');

Route::get('/login', 'HomeController@index')->name('home');

Route::get('/autotest', 'PerfilController@quiz_techero');

Route::group(['prefix' => '{abreviacion}', 'middleware' => 'UrlPais'], function ($abreviacion) {
    Route::get('/home', 'HomeController@index');
    Route::get('/postulaciones', 'PostulacionesController@index');
    Route::get('/', 'ActividadesController@index');
});
