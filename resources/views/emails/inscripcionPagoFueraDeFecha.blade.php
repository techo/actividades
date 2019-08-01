@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        Hola {{$inscripcion->persona->nombres}}
    </p>
    <p>
        Recibimos tu donación pero está fuera de la fecha límite ({{ $inscripcion->actividad->fechaLimitePago }}) para confirmar.
        Es por esto que <b>no quedaste confirmado para participar</b>.
        Lamentamos mucho esto. Si te interesa recuperar esa donación, podés contactarte con el coodinador de la actividad y solicitarle que tramite la devolución:
    </p>

    @if($inscripcion->actividad->coordinador)
        <p>
            <strong>Coordinador de la actividad:</strong>
        </p>
        <p>
            {{$inscripcion->actividad->coordinador->nombres}} {{$inscripcion->actividad->coordinador->apellidoPaterno}}
            <a href="mailto:{{ $inscripcion->actividad->coordinador->mail }}" target="_blank">
                {{ $inscripcion->actividad->coordinador->mail }}
            </a>
        </p>
    @endif

    <p>¡Saludos!</p>
    TECHO - {{$inscripcion->actividad->pais->nombre}}

@endsection
