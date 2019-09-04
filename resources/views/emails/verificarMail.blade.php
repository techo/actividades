@extends('emails.template')

@section('content')
    <p style="font-size: larger">
        Hola {{$persona->nombres}},
    </p>

    <p>
        Clickeá en el botón abajo para verificar tu dirección de email.
    </p>

    <p>
        <a href="{{ $url_verificacion }}" class="button" target="_blank"> Verificar dirección de email </a>
    </p>

    <p>
    	Si no creaste una cuenta, o modificaste tu dirección de email. Podés ignorar este mensaje.
    </p>
@endsection