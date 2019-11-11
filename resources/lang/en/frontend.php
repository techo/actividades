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
    'login' => 'Login',
    'login_to_account' => 'Login to your account',
    'create_account' => '¡I want to volunteer!',
    'forget_password' => 'Forgot your password?',
    'close' => 'close',
    'login_facebook' => 'Facebook Login',
    'login_google' => 'Google Login',
    'mail' => 'Mail',
	'mail_placeholder' => 'Enter your mail',
	'password' => 'Password',
	'password_placeholder' => 'Enter your password',
	'not_a_volunteer' => 'Not yet a volunteer?',
	'volunteer_me' => 'I want to volunteer!',
	'hello' => 'Hi',
	'activities' => 'Activities',
	'my_activities' => 'My Activities',
	'profile' => 'Profile',
	'admin' => 'Admin',
	'help' => 'Help',
	'logout' => 'Logout',
	'login_null' => 'The mail and password is required',


	//loginController
	'login_error' => "Your password or Email isn't correct",

	// reset.blade
	'reset_password' => 'Reset Password',


	//email.blade
	'send_link' => 'Send Link',



	// home
	'title' => "Let's change this reality",
	'which_type_of_activity' => 'In which activity do you wanna apply?',
	'activities_types' => 'Types of activities',
	'view_activities'  =>  'Apply!',
	'welcome' => 'Welcome',

	// registro.vue
	'register' => 'Register',
	'finish' => 'Finish',
	'step_1' => 'STEP 1/3',
	'step_2' => 'STEP 2/3',
	'step_3' => 'STEP 3/3',
    'register_facebook' => 'Register w/ Facebook',
    'register_google' => 'Register w/ Google',
    'create_password' => 'create new password',
    'almost_there' => 'Almost There!',
    'i_accept_the' => 'I accept the ' ,
    'privacy_policy' => 'privacy policies',
    'error_privacy_policy' => 'You must accept the privacy policies to continue',
	'already_register' => 'You are already registered, we wait for you in upcomming activities!! ;)',
	'search_activities' => 'SEARCH ACTIVITIES',
	'link_to_rrss' => 'Confirm link with Social Network',
	'link_rrss_techo' => 'Do you wish to link your Social Network with us?',
	'confirm' => 'Confirm',	

	// verify.blade.php
	'confirm_your_email' => 'Confirm your email address',
    'verify_email_message_1' => 'We have send you an email with a confirmation link',
    'verify_email_message_2' => 'Check your email and confirm your email address to continue',
    'verify_email_message_3' => "If you don't get our email,", 
	'verify_email_resend' => 'click here!',


	// perfil/perfil.vue
	'changes_success'  =>  'The changes have been saved',
	'profile_text_1'  =>  'Here you can change your personal information',
    'profile_text_2'  =>  'Also you can change',
    'profile_text_3'  =>  'your email',
    'personal_data'  =>  'Personal Information',
    'name'  =>  'NAME',
    'surname'  =>  'SURNAME',
    'born_date'  =>  'BORN DATE',
    'gender'  =>  'GENDER',
    'gender_m'  =>  'Masculine',
    'gender_f'  =>  'Feminine',
    'gender_x'  =>  'Other',
    'gender_o'  =>  'Prefer not to say',
    'passport'  =>  'PASSPORT',
    'country'  =>  'COUNTRY',
	'state'  =>  'STATE',
	'city'  =>  'CITY',
	'telpehone'  =>  'TELEPHONE',
	'actual_password'  =>  'ACTUAL PASSWORD',
	'new_password'  =>  'NEW PASSWORD',
    'confirm_new_password'  =>  'NEW PASSWORD CONFIRMATION',
    'platform_notifications_agreement'  =>  'I want to recive notifications about the activities I enroll',
    'techo_notifications_agreement'  =>  'I accept that TECHO is going to contact with for sharing new eventos and campaigns',
	'save'  =>  'Save', 
    'delete_account'  =>  'Delete my account',
 	'account_rrss_text_1' => 'Your are connected via Social Network, to change your password first you have to',
 	'account_rrss_text_2' => 'and then ',
 	'delete_account_message' => "You are trying to delete your account, don't go! Have you tried muting the notifications? ;)",
 	'delete_account_confirm_button' => "DELETE ACCOUNT",




	// perfil/actividades
	'my_activities' => 'My Activities',
	'next_activities' => 'Next Activities', 
    'past_activities' => 'Past Activities',
	'filter_placeholder' => 'Name or location of the activity',


	// perfil/btnMisActividades
	'view_evaluations' => 'View Evaluations',


	// perfil/actividades.vue
	'unenroll_ok' => "You have been unenrolled correctly",
	'enrollment_empty' => "Hey, there is no next inscription, time for apllying! :)",


	// perfil/tarjeta.vue
	'unapply' => 'Unaplly',
	'absent' => 'Absent',
	'evaluations_start_on' => "Evalution starts on",
	'unenroll_title' => 'Unenroll from activity',
	'message_1' => "you're about unenroll yourself from" ,
	'message_2' => ' you can enroll again but data will be erased. ¿Would you like to continue?',
	'unenroll_button' => 'YES, UNENROLL ME',



	// componenets/datatable/filterBar.vue
	'filter_by' => 'Filter by',
	'filter' => 'Filter',
	'delete' => 'Delete',

	// components/datatable.vue
	'empty_records' => "There are no records to show",
	'pagination_detail' => "showing :of  of :total activities",
    
	// actividades/show.blade.php
    'description' => '',
    'not_defined' => 'Not Defined',
    'coordinator' => 'Coordinator',
    'meeting_points' => '¿Where we meet up?',
    'referring' => 'Referring',
    'share' => 'SHARE',


    // actividadesController
    'error' => 'ERROR',
    'closed_inscriptions' => 'Inscrption time is closed',
	'approval_needed' => 'CONFIRM YOUR PARTICIPATION',
	'confirmation_date_is_closed' => 'CONFIRMATION DATE CLOSED',
	'waiting_for_confirmation' => 'WAITING FOR APROVAL',
    'confirmed' => 'CONFIRMED',
	'activity_full' => 'The activity is full',
	'pre_registration' => 'PRE ENRROL',
	'apply_now' => 'APPLY',

	// pagar-paso-1
	'last_step_confirm_by_donation' => 'Last Step!',
	'you_are_pre_registered' => "Now you're pre enroll to the activity:",
	'mail_sended' => 'And we also send and email you andY también te mandamos un mail con toda la información de la actividad! Chequealo!',
	'complete_registration' => 'But First: Confirm your place!',
	'donation_ammount' => 'Donation Ammount',
	'suggested_donation' => 'Suggested Donation: ',
	'suggested_donation_between' => 'Suggested Donation: between ',
	'and' => ' and ',
	'also_you_can' => 'or you can also ',
	'ask_for_grant' => 'ASK FOR A GRANT',
	'go_back' => 'GO BACK',
	'continue' => 'CONTINUE',

	//pagar_paso_2
	'confirm_by_paying' => 'Confirm by paying',
	'ready_for_paying' => "You're ready for paying using the platform!",
	'you_choose' => "You choose to donate:",
	'redirect_pay_platform' => "When clicking you'll be redirected to the paying platform",

	//gracias.blade.php
    'frontend.activity_detail' => 'Details of the activity',
    'inscription_confirmed' => 'Inscription confirmed!',
    'already_inscripted' => 'Already inscrited to ',
	'mail_message' => "We have send you an email with the information about this activity. For watching the activities you are enrolled follow this link ",


	// actividades/index.blade.php
	'index_actividades_text' =>  "50% of the peple involved in our programs have changed their vocation",
	'index_actividades_text_2' => "Apply and see it for yourself",
	'delete_filter' => "Delete Filters",



];
