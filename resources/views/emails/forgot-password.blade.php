@extends('emails.template')

@section('content')
    <p>¡Hola!</p>
    <p>Recibimos un pedido para restablecer tu contraseña.
        Sólo tienes que hacer clic en el siguiente enlace para escoger una nueva:
    </p>

    <p>
        <a href="{{ url('password/reset', $token) }}" style="font-family: Montserrat, sans-serif;text-decoration: none; display: inline-block; font-weight: 700; text-align: center; vertical-align: middle; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; color: #fff; background-color: #0092DD; border-color: #0092DD;" target="_blank" >Establecer nueva contraseña</a>
    </p>

    <p>
        Si no solicitaste cambiar tu contraseña, puedes hacer caso omiso de este mensaje, no cambiaremos tu contraseña
        actual.
    </p>
@endsection

