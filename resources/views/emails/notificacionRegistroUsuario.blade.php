@extends('emails.template')

@section('content')

	<h1>Bienvenido a la plataforma de actividades de TECHO</h1>

    <p style="font-size: larger">
        Hola {{$persona->nombres}},
    </p>

    <p>
        Ya estás registrado en nuestra plataforma, solo te falta verificar tu cuenta de email haciendo click en el enlace:
    </p>

    <a href="{{ $url_verificacion }}" style="font-family: Montserrat, sans-serif;text-decoration: none; display: inline-block; font-weight: 700; text-align: center; vertical-align: middle; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; color: #fff; background-color: #0092DD; border-color: #0092DD;" target="_blank">VERIFICAR EMAIL</a>

    <p>
        Una vez verificada tu cuenta enterate de toda la oferta de voluntariado visitando <a href="{{ url('/actividades') }}">la web</a>.
    </p>

    <p>
        En esta plataforma vas a poder:
        <ul>
        	<li>Inscribirte en las actividades</li>
        	<li>Cancelar tu inscripción en caso que no puedas asistir</li>
        	<li>Confirmar en caso de que la actividad lo necesite</li>
        	<li>Evaluar la actividad en la que participaste y a tus compañeros</li>
        </ul>
    </p>

    <p>
    	Además, podés ver y editar tus datos y tu configuración desde tu <a href="{{ url('/perfil') }}">perfil</a> y consultar tus <a href="{{ url('/perfil/actividades') }}">inscripciones e historial de participación</a>.
    </p>
@endsection