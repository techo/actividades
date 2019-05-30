@extends('emails.template')

@section('content')
    <p style="font-size: larger">
        Hola {{$persona->nombres}},
    </p>
    <p>
        Te informamos que la actividad <strong>{{$actividad->nombreActividad}}</strong> de
        TECHO - {{$pais->nombre}} que iniciaba el
        {{$actividad->fechaInicio->format('d/m/Y')}}, ha sido <strong>CANCELADA</strong>
    </p>
    <p>
        Lamentamos cualquier inconveniente causado y te invitamos a entrar en el sitio de
        Techo para buscar otras actividades.
    </p>
@endsection