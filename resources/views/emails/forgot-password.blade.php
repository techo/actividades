@extends('emails.template')

@section('content')
    <p>¡Hola!</p>
    <p>Recibimos un pedido para restablecer tu contraseña.
        Sólo tienes que hacer clic en el siguiente enlace para escoger una nueva:
    </p>

    <p>
        <a href="{{ url('password/reset', $token) }}">Establecer nueva contraseña</a>
    </p>

    <p>
        Si no solicitaste cambiar tu contraseña, puedes hacer caso omiso de este mensaje, no cambiaremos tu contraseña
        actual.
    </p>
@endsection

