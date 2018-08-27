@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        Hola {{$inscripcion->persona->nombres}},
    </p>
    <p>Te has inscripto para participar en <strong>{{$inscripcion->actividad->nombreActividad}}</strong>
        de TECHO - {{$inscripcion->actividad->pais->nombre}} que inicia
        el {{$inscripcion->actividad->fechaInicio->format('d/m/Y')}} en
        <b>
            {{$inscripcion->actividad->localidad->localidad}}, {{$inscripcion->actividad->provincia->provincia}}
        </b>
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

    <p>
        {{$inscripcion->actividad->mensajeInscripcion}}
    </p>

    @if(strtoupper($inscripcion->actividad->tipo->flujo) == "CONSTRUCCION")
        <p>
            <strong>
                <span style="font-size:20px">Ahora SOLO FALTA UN PASO: <br>
                <span style="color:rgb(255,153,0)">ABONAR A LA ACTIVIDAD </span>
                </span>
            </strong>
        </p>
        <p>
            ¡Tenes tiempo hasta el <b>{{$inscripcion->actividad->fechaFinInscripciones->format('d/m/Y')}}</b>
            para confirmar tu inscripción!
        </p>
        <p>
            Para confirmar tu participación, inicia sesión con tu cuenta y haz click en el botón
            <strong>Confirmar con tu donación</strong> en el <a
                    href="{{ url('/inscripciones/actividad/' . $inscripcion->actividad->idActividad . '/confirmar/donacion/') }}"
            >detalle de la actividad.</a>
        </p>
        <p>
            Te recordamos que el monto mínimo sugerido para abonar es de <b>${{$inscripcion->actividad->montoMin}}</b>, los cuales cubren
            los gastos de traslado, seguro y comida durante la construcción. En el caso que no puedas abonar,
            no queremos que dejes de participar, escríbenos a
            <a href="mailto:problemasdepago.argentina@techo.org" target="_blank">problemasdepago.argentina@techo.org</a>
            para gestionar una PRÓRROGA o <a href="{{ $inscripcion->actividad->beca }}">BECA</a>.
        </p>
    @endif

    <p>¡Te esperamos!</p>

    @if($inscripcion->punto_encuentro)
        <p>
            <strong>
                Punto de encuentro:
            </strong>
        </p>
        <p>
            {{$inscripcion->punto_encuentro->punto}}
            @if(!empty($inscripcion->punto_encuentro->localidad)), {{$inscripcion->punto_encuentro->localidad->localidad}}
            @endif
            @if(!empty($inscripcion->punto_encuentro->provincia))
                ,{{$inscripcion->punto_encuentro->provincia->provincia}}
            @endif
            @if(!empty($inscripcion->punto_encuentro->pais))
                ,{{$inscripcion->punto_encuentro->pais->nombre}}
            @endif
        </p>
        <p>
            <strong>
                Horario:
            </strong>
        </p>
        <p>
            {{ str_limit($inscripcion->punto_encuentro->horario, 5, '')}}
        </p>

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