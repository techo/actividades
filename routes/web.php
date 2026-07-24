<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

//Frontoffice
#Route::get('/', 'HomeController@index')->name('home');



// Universal Links (iOS) y App Links (Android) — deep linking mobile
Route::get('/.well-known/apple-app-site-association', function () {
    return response()->file(public_path('.well-known/apple-app-site-association'), [
        'Content-Type' => 'application/json',
    ]);
});

Route::get('/.well-known/assetlinks.json', function () {
    return response()->file(public_path('.well-known/assetlinks.json'), [
        'Content-Type' => 'application/json',
    ]);
});

Route::get('/cookie/close', function(){
    return response()->json([],200)->cookie('cookie-policy-accepted', 'ok', 60*24*365);
});
Route::get('/carta-voluntariado', function (){ return view('terminos.actividades.show');  });
Route::get('/carta-voluntariado-brasil', function (\Illuminate\Http\Request $request) {
    $actividad = null;
    if ($request->has('actividad')) {
        $actividad = \App\Actividad::with('oficina', 'localidad')->find($request->actividad);
    }
    return view('terminos.actividades.show_brasil', compact('actividad'));
});
Route::get('/carta-voluntariado-paraguay', function (){ return view('terminos.actividades.show_paraguay');  });
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
    Route::get('categorias/{id}/tipos/activas', 'ajax\CategoriasController@tiposActivas');
    // Ambos endpoints exponen PII de Persona: exigen sesión autenticada + acceso al backoffice.
    // (Sus únicos consumidores son pantallas del backoffice.)
    Route::middleware(['verified', 'auth', 'can:accesoBackoffice'])->group(function () {
        Route::get('coordinadores', 'ajax\UsuarioController@getCoordinadores');
        Route::get('personas', 'ajax\UsuarioController@getPersonas');
    });


    Route::get('comunidades', 'ajax\ComunidadesController@index');
    Route::get('comunidades/{idOficina}', 'ajax\ComunidadesController@indexOficina');
    Route::get('comunidades/equipo/{idEquipo}/', 'ajax\ComunidadesController@indexEquipo');
    Route::get('comunidades/{id}', 'ajax\ComunidadesController@show');

    Route::prefix('paises')->group(function () {
        Route::get('/', 'ajax\PaisesController@index');
		Route::get('{id_pais}/provincias', 'ajax\PaisesController@provincias');
		Route::get('{id_pais}/provincias/{id_provincia}/localidades', 'ajax\PaisesController@localidades');
        Route::get('/habilitados', 'ajax\PaisesController@paisesHabilitados');
        Route::get('/propios', 'ajax\PaisesController@paisesPropios');
        Route::get('/conInstitucionesEducativas', 'ajax\PaisesController@paisesConInstitucionesEducativas');
	});
    // `auth` hace el enforcement real (requiere.auth es solo un flag de vista, no bloquea).
    Route::middleware(['auth', 'requiere.auth'])->group(function () {
        Route::prefix('institucionEducativa')->group(function() {
            Route::get('', 'ajax\InstitucionEducativaController@index');
            Route::get('{idInstitucionEducativa}', 'ajax\InstitucionEducativaController@get');
            Route::get('pais/{idPais}', 'ajax\InstitucionEducativaController@porPais');
        });
        
        Route::post('fichaMedica', 'ajax\FichaMedicaController@upsert');
        Route::post('fichaMedica/archivo_medico', 'ajax\FichaMedicaController@uploadArchivoMedico');

        Route::prefix('estudios')->group(function () {
            Route::post('', 'ajax\EstudiosController@create');
            Route::get('/usuario', 'ajax\EstudiosController@estudiosUsuario');
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
		// Rutas públicas: registro y flujo de login social. NO deben exigir sesión.
		Route::get('', function(){
			if(Auth::check()) {
				return Auth::user();
			}
			return '';
		});
        Route::get('validar/{verbo}', 'ajax\UsuarioController@validar');
        Route::post('', 'ajax\UsuarioController@create');
		Route::get('valid_new_mail', 'ajax\UsuarioController@validar_nuevo_mail'); //TODO revisar si se está usando
        Route::put('linkear', 'ajax\UsuarioController@linkear'); // segurizada por sesión (link_social)

        // Rutas sobre los datos del usuario autenticado: requieren sesión real.
        Route::middleware('auth')->group(function () {
            Route::get('perfil', 'ajax\UsuarioController@perfil');
            Route::post('perfil/cambiar_photo', 'ajax\UsuarioController@cambiar_photo');
            Route::put('', 'ajax\UsuarioController@update');
            Route::delete('', 'ajax\UsuarioController@delete'); //Anonimiza cuenta de usuario
            Route::get('inscripciones', 'ajax\UsuarioController@inscripciones');
            Route::delete('inscripciones/{id}', 'ajax\UsuarioController@desinscribir');
        });
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

// throttle: mitiga fuerza bruta / credential stuffing (el login custom no usa ThrottlesLogins).
Route::post('login', 'Auth\LoginController@login')->middleware('throttle:10,1');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register')->middleware('throttle:10,1');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email')->middleware('throttle:6,1');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->middleware('throttle:6,1');

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
Route::post('/actividades/{id}/evaluaciones/impacto', 'EvaluacionesController@evaluarImpacto')->middleware('requiere.auth', 'can:evaluar,App\Actividad,id');

// Flujo de inscripciones

Route::get('/actividades/{id}', 'ActividadesController@show')->middleware('pais');

Route::prefix('/inscripciones/actividad/{id}')->middleware('requiere.auth', 'can:confirmar,App\Actividad,id')->group(function (){
    Route::get('/confirmar/donacion','InscripcionesController@confirmarDonacion');
    Route::post('/confirmar/donacion/checkout','InscripcionesController@donacionCheckout');
    
    Route::post('/confirmar', 'InscripcionesController@confirmar');
});

Route::middleware('auth')->group(function () {
    Route::post('/ajax/inscripcion/voucherPago','InscripcionesController@voucherPago');
    Route::post('/ajax/inscripcion/clearVoucher','InscripcionesController@clearVoucher');
    Route::post('/ajax/inscripcion/becaSolicitud','InscripcionesController@becaSolicitud');

    // Upload de archivo de una respuesta a pregunta tipo 'archivo' (inscripción).
    Route::post('/ajax/inscripcion/pregunta-archivo', 'ajax\PreguntaArchivoController@inscripcion')
        ->middleware('requiere.auth');
});

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

Route::prefix('/admin')->middleware(['verified', 'auth', 'can:accesoBackoffice'])->group(function () {

    // Movido dentro del grupo autenticado: antes quedaba fuera y era accesible sin sesión.
    Route::get('ajax/search/usuarios', 'backoffice\ajax\UsuariosController@usuariosSearch'); //TODO: hack, mejorar

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

    // Campañas
    Route::prefix('/campanas')->middleware(['role:admin'])->group(function () {
        Route::get('', 'backoffice\CampanasController@index');
        Route::get('/crear', 'backoffice\CampanasController@create');
        Route::get('/{id}/preguntas', 'backoffice\CampanasController@preguntas');
        Route::get('/{id}/suscriptos', 'backoffice\CampanasController@suscriptos');
        Route::get('/{id}/exportar', 'backoffice\CampanasController@exportar');
        // Descarga del archivo de una respuesta (pregunta tipo 'archivo') de la campaña.
        Route::get('/{id}/suscripcion-respuesta/{respuesta}/archivo', 'backoffice\ArchivoRespuestaController@campana');
        Route::get('/{id}', 'backoffice\CampanasController@show');
    });
    Route::prefix('ajax/campanas')->middleware(['role:admin'])->group(function () {
        Route::get('', 'backoffice\ajax\CampanasController@index');
        Route::post('', 'backoffice\ajax\CampanasController@store');
        Route::put('/{id}', 'backoffice\ajax\CampanasController@update');
        Route::delete('/{id}', 'backoffice\ajax\CampanasController@destroy');
        Route::get('/{id}/suscriptos', 'backoffice\ajax\CampanasController@suscriptos');
        Route::post('/{id}/convertir', 'backoffice\ajax\CampanasController@convertir');
        Route::post('/{id}/imagen', 'backoffice\ajax\CampanasController@storeImagen');
        // preguntas
        Route::get('/{campana}/preguntas', 'backoffice\ajax\CampaignPreguntasController@index');
        Route::post('/{campana}/preguntas', 'backoffice\ajax\CampaignPreguntasController@store');
        Route::put('/{campana}/preguntas/{preguntaId}', 'backoffice\ajax\CampaignPreguntasController@update');
        Route::delete('/{campana}/preguntas/{preguntaId}', 'backoffice\ajax\CampaignPreguntasController@destroy');
        Route::put('/{campana}/preguntas/{preguntaId}/mover', 'backoffice\ajax\CampaignPreguntasController@mover');
    });
    Route::get('/usuarios/registrar', 'backoffice\UsuariosController@create')->middleware('role:admin');
    Route::post('/usuarios/registrar', 'backoffice\ajax\UsuariosController@store')->middleware('role:admin');
    Route::get('/usuarios/{id}', 'backoffice\UsuariosController@show')->middleware('permission:ver_usuarios');
    Route::post('/usuarios/{id}/editar', 'backoffice\ajax\UsuariosController@update')->middleware('role:admin');
    Route::delete('/usuarios/{id}', 'backoffice\UsuariosController@delete')->middleware('permission:borrar_usuarios');
    Route::post('/usuarios/{persona}/fusionar', 'backoffice\ajax\UsuariosController@fusionar')->middleware('role:admin');


    // panel comunidaddes

    Route::prefix('/comunidades')->middleware(['role:admin|coordinador'])->group(function() {
        Route::get('', 'backoffice\ComunidadesController@index');
        Route::get('/crear', 'backoffice\ComunidadesController@create');
        Route::get('/{idComunidad}', 'backoffice\ComunidadesController@show');
        Route::get('/{idComunidad}/integrantes', 'backoffice\ComunidadesController@showIntegrantes');
        Route::get('/{idComunidad}/actividades', 'backoffice\ComunidadesController@getActividades');
        Route::get('/{idComunidad}/referentes', 'backoffice\ComunidadesController@showReferentes');
        Route::get('/{idComunidad}/redes', 'backoffice\ComunidadesController@showRedes');
        Route::get('/{idComunidad}/ficha', 'backoffice\ComunidadesController@showFicha');
        Route::post('/{idComunidad}/ficha', 'backoffice\ajax\ComunidadFichaController@store');
        Route::post('/{idComunidad}/ficha/{idFicha}', 'backoffice\ajax\ComunidadFichaController@update');

        
        Route::get('/{idComunidad}/coordinacion', 'backoffice\CoordinadorComunidadController@index');

    });
    Route::prefix('ajax/comunidades')->middleware(['role:admin|coordinador'])->group(function() {
        Route::get('', 'backoffice\ajax\ComunidadesController@index');
        Route::put('/{idComunidad}', 'backoffice\ajax\ComunidadesController@update')->middleware('role:admin');
        Route::delete('/{idComunidad}', 'backoffice\ajax\ComunidadesController@destroy')->middleware('role:admin');
        Route::post('/registrar', 'backoffice\ajax\ComunidadesController@store')->middleware('role:admin');

        Route::get('/{idComunidad}/integrantes', 'backoffice\ajax\ComunidadesController@getIntegrantes');
        Route::get('/{idComunidad}/actividades', 'backoffice\ajax\ComunidadesController@getActividades');

        Route::prefix('/{idComunidad}/red')->group(function() {
            Route::get('', 'backoffice\ajax\RedComunidadController@index');  
            Route::post('/crear', 'backoffice\ajax\RedComunidadController@store');  
            Route::put('/{idRedComunidad}', 'backoffice\ajax\RedComunidadController@update');
            Route::delete('/{idRedComunidad}', 'backoffice\ajax\RedComunidadController@delete');
            Route::get('/{idRedComunidad}', 'backoffice\ajax\RedComunidadController@get');  
        });

        Route::prefix('/{idComunidad}/referentes')->group(function() {
            Route::get('', 'backoffice\ajax\ReferenteComunidadController@index');  
            Route::post('/crear', 'backoffice\ajax\ReferenteComunidadController@store');  
            Route::put('/{idReferenteComunidad}', 'backoffice\ajax\ReferenteComunidadController@update');
            Route::delete('/{idReferenteComunidad}', 'backoffice\ajax\ReferenteComunidadController@delete');
            Route::get('/{idReferenteComunidad}', 'backoffice\ajax\ReferenteComunidadController@get');  
        });
        
        //coordinacion
        Route::prefix('/{idComunidad}/coordinacion')->group(function() {
            Route::get('', 'backoffice\ajax\CoordinadorComunidadController@index');
            Route::post('/{idPersona}', 'backoffice\ajax\CoordinadorComunidadController@store');
            Route::delete('/{idCoordinadorComunidad}', 'backoffice\ajax\CoordinadorComunidadController@delete');
        });

    });

    // panel Equipos

    Route::prefix('/equipos')->middleware(['role:admin|coordinador'])->group(function() {
        Route::get('', 'backoffice\EquiposController@index');
        Route::get('/oficina/{idOficina}', 'backoffice\EquiposController@index');
        Route::get('/crear', 'backoffice\EquiposController@create');
        Route::post('/registrar', 'backoffice\EquiposController@store');
        Route::get('/{idEquipo}', 'backoffice\EquiposController@show');
        Route::put('/{idEquipo}', 'backoffice\EquiposController@update');
        Route::delete('/{idEquipo}', 'backoffice\EquiposController@destroy');
        Route::prefix('/{idEquipo}/integrantes')->group(function() {
            Route::get('/todos', 'backoffice\IntegrantesController@indexAll');
            Route::get('/activos', 'backoffice\IntegrantesController@indexActive');
        });

        Route::prefix('/{idEquipo}/seguimiento')->group(function() {
            Route::get('', 'backoffice\EquipoReunionesController@index');
        });

        Route::prefix('/{idEquipo}/coordinacion')->group(function() {
            Route::get('', 'backoffice\CoordinadorEquipoController@index');
        });

    });
    // Ruta de equipos por oficina: accesible también para coordinadores de actividad.
    // La autorización granular se maneja dentro del controller (EquiposController@index).
    Route::get('ajax/equipos/oficina/{idOficina}', 'backoffice\ajax\EquiposController@index');

    Route::prefix('ajax/equipos')->middleware(['role:admin|coordinador'])->group(function() {
        Route::get('', 'backoffice\ajax\EquiposController@index');
        
        Route::prefix('/{idEquipo}/integrante')->group(function() {
            Route::get('/estado/{estado}', 'backoffice\ajax\IntegrantesController@index'); 
            Route::post('/crear', 'backoffice\ajax\IntegrantesController@store');  
            Route::put('/{idIntegrante}', 'backoffice\ajax\IntegrantesController@update');
            Route::delete('/{idIntegrante}', 'backoffice\ajax\IntegrantesController@delete');
            Route::get('/{idIntegrante}', 'backoffice\ajax\IntegrantesController@get');  
            Route::post('/{idIntegrante}/archivos', 'backoffice\ajax\IntegrantesController@uploadArchivos');  
        });

        Route::prefix('/{idEquipo}/reuniones')->group(function() {
            Route::get('', 'backoffice\ajax\EquipoReunionesController@index'); 
            Route::post('/crear', 'backoffice\ajax\EquipoReunionesController@store');  
            Route::get('/{idReunion}', 'backoffice\ajax\EquipoReunionesController@get');  
            Route::put('/{idReunion}', 'backoffice\ajax\EquipoReunionesController@update');
            Route::delete('/{idReunion}', 'backoffice\ajax\EquipoReunionesController@delete');
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
    Route::get('/ajax/usuarios/{id}/estudios', 'backoffice\ajax\UsuariosController@estudios');

    Route::get('/roles', 'backoffice\UsuariosRolesController@index')->middleware('permission:asignar_roles'); //TODO: Mejorar la nomenclatura de la ruta
    Route::get('/ajax/roles', 'backoffice\ajax\UsuariosRolesController@index')->middleware('permission:ver_usuarios'); //TODO: Mejorar la nomenclatura de la ruta
    Route::post('/roles/usuario/{id}', 'backoffice\UsuariosRolesController@update')->middleware('permission:asignar_roles');

    Route::get('/ajax/actividades/usuario', 'backoffice\ajax\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades', 'backoffice\ActividadesController@index')->middleware('role:admin');
    Route::get('/actividades/crear', 'backoffice\ActividadesController@create');
    Route::post('/actividades/crear', 'backoffice\ActividadesController@store');
    Route::post('/ajax/actividades/{actividad}', 'backoffice\ActividadesController@update')->middleware('can:editar,App\Actividad,actividad');
    Route::get('/actividades/usuario', 'backoffice\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades/usuario/exportar', 'backoffice\ReportController@exportarMisActividades')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/actividades/exportar', 'backoffice\ReportController@exportarActividades')->middleware('role:admin');
    Route::get('/suscriptos/exportar', 'backoffice\ReportController@exportarSuscriptos')->middleware('role:admin');
    
    Route::get('/actividades/{id}/exportar-evaluaciones-voluntarios', 'backoffice\ReportController@exportarEvaluacionesPersonas');
    Route::get('/actividades/{id}/exportar-evaluaciones', 'backoffice\ReportController@exportarEvaluacionesActividad');
    Route::get('/actividades/{id}/exportar-evaluaciones-impacto', 'backoffice\ReportController@exportarEvaluacionesImpacto');
    //vista de actividad
    Route::get('/actividades/{id}', 'backoffice\ActividadesController@show')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/puntos', 'backoffice\ActividadesController@puntos')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/inscripciones', 'backoffice\ActividadesController@inscripciones')->middleware('can:ver,App\Actividad,id');

    // Descarga del archivo de una respuesta (pregunta tipo 'archivo') de la actividad.
    Route::get('/actividades/{id}/respuesta/{respuesta}/archivo', 'backoffice\ArchivoRespuestaController@inscripcion')
        ->middleware('can:ver,App\Actividad,id');

    Route::prefix('/actividades/{id}/informe_cierre')->middleware(['role:admin'])->group(function() {
        Route::get('', 'backoffice\ActividadInformeCierreController@index');
        Route::get('/{idInformeCierre}', 'backoffice\ActividadInformeCierreController@get');
        Route::post('', 'backoffice\ActividadInformeCierreController@upsert');
    });

    Route::prefix('ajax/actividades/{id}/informe_cierre')->middleware(['role:admin'])->group(function() {
        Route::get('', 'backoffice\ActividadInformeCierreController@getInformes');
        Route::get('/{idInformeCierre}', 'backoffice\ActividadInformeCierreController@get');
        Route::post('', 'backoffice\ActividadInformeCierreController@upsert');
        Route::delete('/{idInformeCierre}', 'backoffice\ActividadInformeCierreController@delete');
    });

    Route::get('/actividades/{actividad}/inscripcion/{inscripcion}/persona/{persona}', 'backoffice\ActividadesController@confirmarInscripcion')->middleware('can:ver,App\Actividad,actividad');
    Route::get('/actividades/{id}/grupos', 'backoffice\ActividadesController@grupos')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/evaluaciones', 'backoffice\ActividadesController@evaluaciones')->middleware('can:ver,App\Actividad,id');
    Route::get('/actividades/{id}/accesos', 'backoffice\ActividadesController@accesos')->middleware('can:ver,App\Actividad,id');
    
    Route::get('/actividades/{id}/jornadas', 'backoffice\ActividadesController@jornadas')->middleware('can:ver,App\Actividad,id');
    Route::get('/ajax/actividades/{id}/jornadas', 'backoffice\ajax\ActividadesController@jornadas');

    Route::get('/actividades/{actividad}/preguntas', 'backoffice\ActividadesController@preguntas')->middleware('can:ver,actividad');
    Route::get('/ajax/actividades/{actividad}/preguntas', 'backoffice\ajax\ActividadPreguntasController@index')->middleware('can:ver,actividad');
    Route::post('/ajax/actividades/{actividad}/preguntas', 'backoffice\ajax\ActividadPreguntasController@store')->middleware('can:ver,actividad');
    Route::put('/ajax/actividades/{actividad}/preguntas/{preguntaId}', 'backoffice\ajax\ActividadPreguntasController@update')->middleware('can:ver,actividad');
    Route::delete('/ajax/actividades/{actividad}/preguntas/{preguntaId}', 'backoffice\ajax\ActividadPreguntasController@destroy')->middleware('can:ver,actividad');
    Route::put('/ajax/actividades/{actividad}/preguntas/{preguntaId}/mover', 'backoffice\ajax\ActividadPreguntasController@mover')->middleware('can:ver,actividad');

    Route::post('ajax/actividades/{id}/jornadas', 'backoffice\ajax\JornadasController@store')->middleware('can:ver,App\Actividad,id');;


    Route::put('/ajax/actividades/{id}/jornadas/{jornada}', 'backoffice\ajax\JornadasController@update');
    Route::delete('/ajax/actividades/{id}/jornadas/{jornada}', 'backoffice\ajax\JornadasController@delete');
    
    Route::get('/ajax/actividades/{id}', 'backoffice\ActividadesController@actividad')->middleware('can:ver,App\Actividad,id');
    Route::get('/ajax/actividades/{id}/accesos', 'backoffice\ActividadesController@coordinadores')->middleware('can:ver,App\Actividad,id');
    Route::post('/ajax/actividades/{actividad}/accesos/{persona}', 'backoffice\ActividadesController@guardarCoordinador')->middleware('can:editar,App\Actividad,actividad');
    Route::post('/ajax/actividades/{actividad}/accesos/{coordinador}/borrar', 'backoffice\ActividadesController@eliminarCoordinador')->middleware('can:editar,App\Actividad,actividad');
    Route::post('/ajax/actividades/{actividad}/accesos/{coordinador}/activaWhatsapp', 'backoffice\ajax\ActividadesController@activaWhatsappAccesos')->middleware('can:editar,App\Actividad,actividad');
    Route::delete('/actividades/{id}', 'backoffice\ActividadesController@destroy')->middleware('can:borrar,App\Actividad,id');
    Route::get('/actividades/{id}/editar', 'backoffice\ActividadesController@edit')->middleware('can:editar,App\Actividad,id');
    Route::post('/actividades/{id}/editar', 'backoffice\ActividadesController@update')->middleware('can:editar,App\Actividad,id');
    Route::get('/actividades/{id}/inscripciones/exportar', 'backoffice\ReportController@exportarInscripcionesActividad')->middleware('can:verInscripciones,App\Inscripcion,id');

    // Listados con columnas configurables (registry: config/listados.php).
    // La autorización por list_key/context la resuelve el propio controller.
    Route::prefix('ajax/listados/{listKey}/{contextId}')->group(function () {
        Route::get('/config', 'backoffice\ajax\ListadoConfigController@config');
        Route::get('/count', 'backoffice\ajax\ListadoConfigController@count');
        Route::get('/facets', 'backoffice\ajax\ListadoConfigController@facets');
        Route::get('/vistas', 'backoffice\ajax\ListadoConfigController@vistas');
        Route::post('/vistas', 'backoffice\ajax\ListadoConfigController@guardarVista');
        Route::put('/vistas/{vistaId}', 'backoffice\ajax\ListadoConfigController@actualizarVista');
        Route::delete('/vistas/{vistaId}', 'backoffice\ajax\ListadoConfigController@eliminarVista');
        Route::put('/preferencias', 'backoffice\ajax\ListadoConfigController@guardarPreferencias');
        Route::post('/columnas', 'backoffice\ajax\ListadoConfigController@crearColumna');
        Route::put('/columnas/{columnaId}', 'backoffice\ajax\ListadoConfigController@actualizarColumna');
        Route::delete('/columnas/{columnaId}', 'backoffice\ajax\ListadoConfigController@eliminarColumna');
        Route::put('/columnas/{columnaId}/valores/{recordId}', 'backoffice\ajax\ListadoConfigController@guardarValor');
    });

    Route::get('/ajax/actividades/{id}/inscripciones', 'backoffice\ajax\InscripcionesController@index')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones', 'backoffice\ajax\InscripcionesController@store')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::get('/ajax/actividades/{id}/grupos/getInscriptos', 'backoffice\ajax\InscripcionesController@getInscriptos')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::get('/ajax/actividades/{id}/puntos', 'backoffice\ajax\ActividadesController@puntos')->middleware('can:editar,App\Actividad,id');

    Route::post('/ajax/actividades/{id}/imagen-tarjeta', 'backoffice\ajax\ActividadesController@storeImagenTarjeta')->middleware('can:editar,App\Actividad,id');
    Route::post('/ajax/actividades/{id}/imagen-destacada', 'backoffice\ajax\ActividadesController@storeImagenDestacada')->middleware('can:editar,App\Actividad,id');

    Route::get('/ajax/actividades/{id}/puntos/{punto}', 'backoffice\ajax\PuntosController@show');
    Route::post('/ajax/actividades/{id}/puntos', 'backoffice\ajax\PuntosController@store')->middleware('can:editar,App\Actividad,id');
    Route::post('/ajax/actividades/{id}/puntos/{punto}', 'backoffice\ajax\PuntosController@update')->middleware('can:editar,App\Actividad,id');
    Route::delete('/ajax/actividades/{id}/puntos/{punto}', 'backoffice\ajax\PuntosController@delete')->middleware('can:editar,App\Actividad,id');

    Route::post('/ajax/actividades/{id}/enviar-evaluaciones', 'backoffice\ajax\EvaluacionesController@enviar')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/stats', 'backoffice\ajax\EvaluacionesController@getActividadStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/chartdata', 'backoffice\ajax\EvaluacionesController@getActividadChartData')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/general/stats', 'backoffice\ajax\EvaluacionesController@getGeneralStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/voluntarios/stats', 'backoffice\ajax\EvaluacionesController@getVoluntariosStats')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/voluntarios/chartdata', 'backoffice\ajax\EvaluacionesController@getVoluntariosChartData')->middleware('can:verInscripciones,App\Inscripcion,id'); //TODO: Revisar el middleware debería ser un permiso relacionado con evaluaciones
    Route::get('/ajax/actividades/{id}/evaluaciones/comentarios', 'backoffice\ajax\EvaluacionesController@getComentarios')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/ajax/actividades/{id}/evaluaciones/tags', 'backoffice\ajax\EvaluacionesController@getTagsResumen')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/ajax/actividades/{id}/evaluaciones/competencias', 'backoffice\ajax\EvaluacionesController@getCompetenciasStats')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/ajax/actividades/{id}/evaluaciones/impacto', 'backoffice\ajax\EvaluacionesController@getImpactoStats')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/grupos/cambiar/grupo', 'backoffice\ajax\GruposActividadesController@update')->middleware('can:editar,App\Actividad,id');
    Route::post('/ajax/actividades/{id}/grupos/cambiar/rol', 'backoffice\ajax\GruposActividadesController@updateRol')->middleware('can:editar,App\Actividad,id');
    Route::post('/ajax/actividades/{id}/grupos/cambiar/link', 'backoffice\ajax\GruposActividadesController@updateLink')->middleware('can:editar,App\Actividad,id');
    Route::post('/ajax/actividades/{id}/grupos/borrar', 'backoffice\ajax\GruposActividadesController@delete')->middleware('can:editar,App\Actividad,id');
    Route::get('/ajax/actividades/{id}/grupos', 'backoffice\ajax\ActividadesController@grupos')->middleware('can:ver,App\Actividad,id');
    Route::post('/ajax/actividades/{id}/clonar', 'backoffice\ActividadesController@clonar')->middleware('can:editar,App\Actividad,id');

    Route::post('/ajax/actividades/{id}/inscripciones/desinscribir',
    'backoffice\ajax\InscripcionesController@desinscribir')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/rol', 'backoffice\ajax\InscripcionesController@asignarRol')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/grupo', 'backoffice\ajax\InscripcionesController@asignarGrupo')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/asignar/punto', 'backoffice\ajax\InscripcionesController@asignarPunto')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/confirmacion', 'backoffice\ajax\InscripcionesController@cambiarConfirmacion')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/pago', 'backoffice\ajax\InscripcionesController@cambiarPago')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/cambiar/asistencia', 'backoffice\ajax\InscripcionesController@cambiarAsistencia')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/rechazar/voucher', 'backoffice\ajax\InscripcionesController@rechazarVoucher')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::post('/ajax/actividades/{id}/inscripciones/rechazar/documento', 'backoffice\ajax\InscripcionesController@rechazarDocumento')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/{inscripcion}', 'backoffice\ajax\InscripcionesController@update')->middleware('can:verInscripciones,App\Inscripcion,id');

    Route::post('/ajax/actividades/{id}/inscripciones/{inscripcion}/eliminar', 'backoffice\ajax\InscripcionesController@destroy')->middleware('can:verInscripciones,App\Inscripcion,id');
    

    Route::post('/ajax/actividades/{id}/inscripciones/procesar/archivo', 'backoffice\ajax\InscripcionesController@procesarArchivo')->middleware('can:verInscripciones,App\Inscripcion,id');
    Route::get('/inscripciones/importar/template', 'backoffice\InscripcionesController@descargarTemplate'); //TODO: segurizar
    Route::get('/ajax/actividades', 'backoffice\ajax\ActividadesController@index');

    Route::get('/ajax/actividades/usuario', 'backoffice\ajax\CoordinadorActividadesController@index')->middleware('can:indexMisActividades,App\Actividad');
    Route::get('/ajax/oficinas', 'backoffice\ajax\OficinasController@getOficinas');
    Route::get('/ajax/oficinas/pais/{idpais}', 'backoffice\ajax\OficinasController@getOficinasPais');
    Route::get('/ajax/areas', 'backoffice\ajax\AreasController@index');
    Route::get('/ajax/roles-integrante', 'backoffice\ajax\RolesIntegranteController@index');
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

    // Dashboard general de evaluaciones
    Route::get('/ajax/estadisticas/evaluaciones/general-stats',   'backoffice\EstadisticasController@evaluaciones_general_stats');
    Route::get('/ajax/estadisticas/evaluaciones/actividad-stats', 'backoffice\EstadisticasController@evaluaciones_actividad_stats');
    Route::get('/ajax/estadisticas/evaluaciones/histograma',      'backoffice\EstadisticasController@evaluaciones_histograma');
    Route::get('/ajax/estadisticas/evaluaciones/comentarios',     'backoffice\EstadisticasController@evaluaciones_comentarios_general');
    Route::get('/ajax/estadisticas/evaluaciones/tags',            'backoffice\EstadisticasController@evaluaciones_tags_general');
    Route::get('/ajax/estadisticas/evaluaciones/competencias',    'backoffice\EstadisticasController@evaluaciones_competencias_general');
    Route::get('/ajax/estadisticas/evaluaciones/impacto',         'backoffice\EstadisticasController@evaluaciones_impacto_general');

    Route::get('/ajax/estadisticas/inscripciones/exportar', 'backoffice\ReportController@ExportarInscripciones')->middleware('role:admin');;
    Route::get('/ajax/estadisticas/inscripciones/personas/exportar', 'backoffice\ReportController@exportarPersonasInscriptas')->middleware('role:admin');;
    Route::get('/ajax/estadisticas/evaluaciones/exportar', 'backoffice\ReportController@exportarEvaluacionesGenerales')->middleware('role:admin');;
    Route::get('/ajax/estadisticas/evaluadores/exportar', 'backoffice\ReportController@exportarEvaluadoresGenerales')->middleware('role:admin');;

    // Exportaciones del nuevo dashboard general
    Route::get('/ajax/estadisticas/evaluaciones/exportar-actividad',  'backoffice\ReportController@exportarEvaluacionesActividadGeneral')->middleware('role:admin');
    Route::get('/ajax/estadisticas/evaluaciones/exportar-personas',   'backoffice\ReportController@exportarEvaluacionesPersonasGeneral')->middleware('role:admin');
    Route::get('/ajax/estadisticas/evaluaciones/exportar-impacto',    'backoffice\ReportController@exportarEvaluacionesImpactoGeneral')->middleware('role:admin');

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

// Stripe — rutas autenticadas
Route::middleware('auth')->prefix('stripe')->group(function () {
    Route::post('/{idInscripcion}/checkout', 'StripeController@createCheckout')->name('stripe.checkout');
    Route::get('/success', 'StripeController@success')->name('stripe.success');
    Route::get('/cancel/{idInscripcion}', 'StripeController@cancel')->name('stripe.cancel');
});

// Stripe webhook — sin auth ni CSRF (validado por firma Stripe)
Route::post('/stripe/webhook/{paisId}', 'StripeController@webhook')->name('stripe.webhook');

Route::get('locale/{locale}', function($locale){
    Session::put('locale',$locale);
    return redirect()->back();
});


    Route::get('/home', 'HomeController@index');
    Route::get('/', 'HomeController@multiPais');
    // Route::get('/', 'ActividadesController@index');
    Route::get('/actividades', 'ActividadesController@index');

Route::get('/login', 'HomeController@index')->name('home');

Route::get('/autotest', 'PerfilController@quiz_techero');


Route::group(['prefix' => '{abreviacion}', 'middleware' => 'UrlPais'], function ($abreviacion) {
    Route::get('/suscribe', 'SuscribeController@get');
    Route::post('/suscribe', 'SuscribeController@create');
    // Upload de archivo de una respuesta a pregunta tipo 'archivo' (campaña).
    // Público, igual que el submit de suscripción anónima; con throttle porque
    // no exige autenticación.
    Route::post('/suscribe/pregunta-archivo', 'ajax\PreguntaArchivoController@campana')
        ->middleware('throttle:30,1');
    Route::get('/check-email', 'SuscribeController@checkEmail');
    Route::get('/filtro', 'ActividadesController@index');
    Route::get('/', 'HomeController@index');
    Route::get('/postulaciones', 'PostulacionesController@index');
    Route::get('/campania/{id}', 'CampaniaController@show');
    Route::get('/colecta/{id}', 'CampaniaController@show');
    Route::get('/captacion/{id}', 'CampaniaController@show');
});
