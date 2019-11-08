<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Frontend Language Lines
	|--------------------------------------------------------------------------
	|
	|
	*/

	// login.vue
    'login' => 'Ingresar',
    'login_to_account' => 'Ingresar a mi cuenta',
    'create_account' => '¡Quiero ser voluntario!',
    'forget_password' => 'Olvidé mi contraseña',
    'close' => 'Cerrar',
    'forget_password' => 'Olvidé mi contraseña',
    'login_facebook' => 'Ingresá con Facebook',
    'login_google' => 'Ingresá con Google',
    'mail' => 'Mail',
	'mail_placeholder' => 'Ingresá tu Mail',
	'password' => 'Contraseña',
	'password_placeholder' => 'Ingresá tu contraseña',
	'not_a_volunteer' => '¿TODAVÍA NO SOS VOLUNTARIO DE TECHO?',
	'volunteer_me' => '¡Quiero ser voluntario!',
	'hello' => 'Hola',
	'activities' => 'Actividades',
	'my_activities' => 'Mis Actividades',
	'profile' => 'Perfil',
	'admin' => 'Admin',
	'help' => 'Ayuda',
	'logout' => 'Salir',
	'login_null' => 'El Correo electrónico y la contraseña son requeridos',


	//loginController
	'login_error' => 'El correo electrónico y/o la contraseña es incorrecta',


	// reset.blade
	'reset_password' => 'Restablecer Contraseña',

	//email.blade
	'send_link' => 'Enviar enlace',


	// home
	'title' => 'Transformemos esta realidad',
	'which_type_of_activity' => '¿En qué actividad querés participar?',
	'activities_types' => 'Tipos de Actividad',
	'view_activities'  =>  'Anotame',
	'welcome' => 'Bienvenido',


	// perfil/perfil.vue
	'changes_success'  =>  'Los cambios fueron guardados con éxito.',
	'profile_text_1'  =>  'Aquí podrás realizar cambios en tu pérfil. Modifica tu contraseña y datos personales.',
    'profile_text_2'  =>  'También podés',
    'profile_text_3'  =>  'cambiar tu dirección de email',
    'personal_data'  =>  'Datos personales',
    'name'  =>  'NOMBRE*',
    'surname'  =>  'APELLIDO*',
    'born_date'  =>  'NACIMIENTO*',
    'gender'  =>  'GENERO*',
    'gender_m'  =>  'Masculino',
    'gender_x'  =>  'Other',
    'gender_o'  =>  'Prefiero no decirlo',
    'passport'  =>  'NRO. DE DNI / PASAPORTE*',
    'country'  =>  'PAIS*',
	'state'  =>  'PROVINCIA',
	'city'  =>  'LOCALIDAD',
	'telphone'  =>  'TELEFONO*',
	'actual_password'  =>  'CONTRASEÑA ACTUAL*',
	'new_password'  =>  'NUEVA CONTRASEÑA*',
    'confirm_new_password'  =>  'CONFIRMAR CONTRASEÑA*',
    'platform_notifications_agreement'  =>  'Recibir notificaciones operativas de la plataforma (necesario para mantenerte informado de las actividades en las que participas)',
    'techo_notifications_agreement'  =>  'Acepto que TECHO se contacte conmigo para notificarme de eventos y campañas',
	'save'  =>  'Guardar', 
    'delete_account'  =>  'Eliminar mi cuenta',
 	'account_rrss_text_1' => 'Tu cuenta está vinculada a una red social, para cambiar tu contraseña debes',
 	'account_rrss_text_2' => 'y hacer click en',
 	'delete_account_message' => "Estás por eliminar tu cuenta de esta plataforma. La acción no podrá deshacerse. ¿Deseas continuar?",
 	'delete_account_confirm_button' =>'SI, ELIMINAR',



	// perfil/actividades
	'my_activities' => 'Mis Actividades',
	'next_activities' => 'Próximas Actividades', 
    'past_activities' => 'Actividades Pasadas',
	'filter_placeholder' => 'Nombre o localidad de la actividad',


	// perfil/btnMisActividades
	'view_evaluations' => 'Ver Evaluaciones',


	// perfil/actividades.vue
	'unenroll_ok' => "Te has desinscripto correctamente de la actividad.",
	'enrollment_empty' => "Ey, ninguna inscripción futura, tiempo de buscar una! :)",


	// perfil/tarjeta.vue
	'unenroll_ok' => "Te has desinscripto correctamente de la actividad.",
	'enrollment_empty' => "Ey, ninguna inscripción futura, tiempo de buscar una! :)",
	'unapply' => 'Desinscribirme',
	'absent' => 'Ausente',
	'evaluations_start_on' => 'Las evaluaciones comienzan el',
	'unenroll_title' => 'DESINSCRIBIRME DE ACTIVIDAD',
	'message_1' => 'Estás por desinscribirte de la actividad',
	'message_2' => ' se borrarán tus datos para participar. Puedes inscribirte cuando desees. ¿Deseas continuar?',
	'unenroll_button' => 'SI, DESINSCRIBIRME',


	// componenets/datatable/filterBar.vue
	'filter_by' => 'Filtrar por',
	'filter' => 'Filtrar',
	'delete' => 'Borrar',

	// components/datatable.vue
	'empty_records' => "No hay registros para mostrar",
	'pagination_detail' => "Mostrando :of  de :total actividades",
    
	// actividades/show.blade.php
    'description' => '',
    'not_defined' => 'No definido',
    'coordinator' => 'Coordina',
    'meeting_points' => '¿Dónde nos encontramos?',
    'referring' => 'Referente',
    'share' => 'COMPARTIR',


    // actividadesController
    'error' => 'ERROR',
    'closed_inscriptions' => 'El período de inscripción está cerrado',
	'approval_needed' => 'CONFIRMÁ TU PARTICIPACIÓN',
	'confirmation_date_is_closed' => 'FECHA DE CONFIRMACIÓN VENCIDA',
	'waiting_for_confirmation' => 'ESPERAR CONFIRMACIÓN',
    'confirmed' => 'CONFIRMADO',
	'activity_full' => 'La actividad no tiene más cupos',
	'pre_registration' => 'PREINSCRIBIRME',
	'apply_now' => 'INSCRIBIRME',

	// pagar-paso-1
	'last_step_confirm_by_donation' => '¡Sólo queda un paso!',
	'you_are_pre_registered' => 'Ya estás pre inscripto a',
	'mail_sended' => 'Y también te mandamos un mail con toda la información de la actividad! Chequealo!',
	'complete_registration' => 'Pero antes: Confirmá tu lugar!',
	'donation_ammount' => 'MONTO A DONAR',
	'suggested_donation' => 'Donación sugerida: ',
	'suggested_donation_between' => 'Donación sugerida: Entre ',
	'and' => ' y ',
	'also_you_can' => 'o también podés ',
	'ask_for_grant' => 'SOLICITAR UNA BECA',
	'go_back' => 'VOLVER',
	'continue' => 'SIGUIENTE',
	'cancel' => 'SIGUIENTE',
	'unenroll' => 'SIGUIENTE',

	//pagar_paso_2
	'confirm_by_paying' => 'Confirma con tu pago',
	'ready_for_paying' => '¡Listo para pagar por la plataforma!',
	'you_choose' => 'Elegiste donar:',
	'redirect_pay_platform' => 'Al hacer click se te redirigirá a la plataforma de pago',

	//gracias.blade.php
    'frontend.activity_detail' => 'Detalle de Actividad',
    'inscription_confirmed' => '¡Inscripción confirmada!',
    'already_inscripted' => 'Ya estás inscripto a ',
	'mail_message' => 'Te enviamos un mail con más información sobre esta actividad. Para ver las actividades a las que estás inscripto y modificarlas ingresá a ',


	// actividades/index.blade.php
	'index_actividades_text' =>  'Si te da lo mismo, estás haciendo mal las cuentas',
	'index_actividades_text_2' => 'Anotate y participá',
	'delete_filter' => 'Borrar Filtros',



];
