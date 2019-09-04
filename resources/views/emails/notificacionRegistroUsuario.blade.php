@extends('emails.template')

@section('content')

	<h1>Bienvenido a la plataforma de actividadesde de TECHO</h1>

    <p style="font-size: larger">
        Hola {{$persona->nombres}},
    </p>

    <p>
        Ya estás registrado en nuestra plataforma, solo te falta verificar tu cuenta de email haciendo click en el botón.
    </p>

    <a href="{{ $url_verificacion }}" class="button" target="_blank">Verificar email</a>

    <p>
        Enterate de toda la oferta de voluntariado visitando <a href="{{ url('/actividades') }}">el sitio</a>.
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