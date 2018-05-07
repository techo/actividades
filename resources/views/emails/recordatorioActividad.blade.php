@extends('emails.template')

@section('content')

    <p>
        Hola {{$inscripcion->persona->nombres}},
    </p>

    <p>
        Te recordamos que te has inscrito para participar en
        <strong>{{$inscripcion->actividad->nombreActividad}}</strong> de TECHO
        - {{$inscripcion->actividad->pais->nombre}}
        que inicia el {{$inscripcion->actividad->fechaInicio->format('d/m/Y')}} en
        {{$inscripcion->actividad->localidad->localidad}}, {{$inscripcion->actividad->provincia->provincia}}.
    </p>

    @if($inscripcion->actividad->coordinador)
        <p>
            <strong>Coordinador de la actividad:</strong>
            {{$inscripcion->actividad->coordinador->nombres}} {{$inscripcion->actividad->coordinador->apellidoPaterno}}
            <a href="mailto:{{ $inscripcion->actividad->coordinador->mail }}" target="_blank">
                {{ $inscripcion->actividad->coordinador->mail }}
            </a>
        </p>
    @endif

    <p>
        {{$inscripcion->actividad->mensajeInscripcion}}
    </p>

    <p>
        <strong>¡Te esperamos!</strong>
    </p>
    @if($inscripcion->punto_encuentro)
        <p>
            <strong>
                Punto de encuentro:
            </strong>
        </p>
        <p>
            {{$inscripcion->punto_encuentro->punto}}
            {{$inscripcion->punto_encuentro->localidad->localidad}},
            {{$inscripcion->punto_encuentro->provincia->provincia}},
            {{$inscripcion->punto_encuentro->pais->nombre}}
        </p>
        <p>
            <strong>
                Horario:
            </strong>
        </p>
        {{$inscripcion->punto_encuentro->horario}}

        @if($inscripcion->punto_encuentro->responsable)
            <p>
                <strong>
                    Coordinador del punto de encuentro:
                </strong>
            </p>
            <p>
                {{$inscripcion->punto_encuentro->responsable->nombres}}
                {{$inscripcion->punto_encuentro->responsable->apellidoPaterno}}
                <a href="mailto:{{ $inscripcion->punto_encuentro->responsable->mail }}" target="_blank">
                    {{ $inscripcion->actividad->coordinador->mail }}
                </a>

            </p>
        @endif
    @endif

@endsection