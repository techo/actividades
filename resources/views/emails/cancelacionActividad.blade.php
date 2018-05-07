@extends('emails.template')

@section('content')
    <p>
        Hola {{$inscripcion->persona->nombres}},
    </p>
    <p>
        Te informamos que la actividad <strong>{{$inscripcion->actividad->nombreActividad}}</strong> de
        TECHO - {{$inscripcion->actividad->pais->nombre}} que iniciaba el
        {{$inscripcion->actividad->fechaInicio->format('d/m/Y')}}, ha sido <strong>CANCELADA</strong>
    </p>
    <p>
        <strong>
            ¡Lamentamos cualquier inconveniente causado y te invitamos a entrar en el sitio de
            Techo para buscar otras actividades!
        </strong>
    </p>
@endsection