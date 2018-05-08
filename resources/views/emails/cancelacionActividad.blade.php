@extends('emails.template')

@section('content')
    <p style="font-size: larger">
        Hola {{$inscripcion->persona->nombres}},
    </p>
    <p>
        Te informamos que la actividad <strong>{{$inscripcion->actividad->nombreActividad}}</strong> de
        TECHO - {{$inscripcion->actividad->pais->nombre}} que iniciaba el
        {{$inscripcion->actividad->fechaInicio->format('d/m/Y')}}, ha sido <strong>CANCELADA</strong>
    </p>
    <p>
        Lamentamos cualquier inconveniente causado y te invitamos a entrar en el sitio de
        Techo para buscar otras actividades.
    </p>
@endsection