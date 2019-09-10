@extends('emails.template')

@section('content')
    <p style="font-size: larger">
        Hola {{$persona->nombres}},
    </p>

    <p>
        Este email es para verificar tu dirección de email:
    </p>

    <p>
        <a href="{{ $url_verificacion }}" style="font-family: Montserrat, sans-serif;text-decoration: none; display: inline-block; font-weight: 700; text-align: center; vertical-align: middle; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; color: #fff; background-color: #0092DD; border-color: #0092DD;" target="_blank"> VERIFICAR EMAIL </a>
    </p>

@endsection