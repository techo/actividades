@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        Hola {{$inscripcion->persona->nombres}}
    </p>
    <p>Te has preinscripto para participar: <strong>{{$inscripcion->actividad->nombreActividad}}</strong>
        Inicia el 
            <strong>{{$inscripcion->actividad->localidad->localidad}}, {{$inscripcion->actividad->provincia->provincia}}</strong>               <strong>
                {{$inscripcion->actividad->fechaInicio->format('d/m/Y H:i')}}
            </strong> en
            <strong>
                @if($inscripcion->actividad->idLocalidad)
                    {{$inscripcion->actividad->localidad->localidad}}, 
                @endif
                {{$inscripcion->actividad->provincia->provincia}}
            </strong>
    </p>

        <p>
            <strong>
                <span style="color:rgb(255,153,0)">SOLO FALTA CONFIRMAR CON TU DONACIÓN</span>
            </strong>
        </p>
        <p>
            ¡Tenés tiempo hasta el <b>{{$inscripcion->actividad->fechaFinInscripciones->format('d/m/Y')}}</b>!
        </p>
        <p>
            Para confirmar tu participación, haz click en el botón
            <strong>Confirmar con tu donación</strong> en la <a href="{{ url('/actividades/' . $inscripcion->actividad->idActividad ) }}" >actividad.</a>
        </p>
        <p>
            Te recordamos que el monto mínimo sugerido para abonar es de <b>{{ number_format($inscripcion->actividad->montoMin,0) }} {{$inscripcion->actividad->moneda}}</b>, los cuales cubren
            los gastos de traslado, seguro y comida durante la construcción.
        </p>
        <p>
            En el caso que no puedas abonar, no queremos que dejes de participar,
            @if(!empty($inscripcion->actividad->beca))
                solicitá una <a href="{{ $inscripcion->actividad->beca }}">BECA</a>.
            @else
                ponete en contacto con el coodinador de la actividad para gestionar una BECA
            @endif
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
      <strong>
          Mensaje del coordinador:
      </strong>
        {{$inscripcion->actividad->mensajeInscripcion}}
    </p>



    @if($inscripcion->punto_encuentro)
        <p>
          <strong>
              Punto de encuentro:
          </strong>
            {{$inscripcion->punto_encuentro->punto}} ({{ str_limit($inscripcion->punto_encuentro->horario, 5, '')}}hs)
            @if($inscripcion->punto_encuentro->idLocalidad)
                {{$inscripcion->punto_encuentro->localidad->localidad}},
            @endif
            {{$inscripcion->punto_encuentro->provincia->provincia}}, {{$inscripcion->punto_encuentro->pais->nombre}}
        </p>

        @if($inscripcion->punto_encuentro->responsable)
            <p>
                <strong>
                    Referente del punto de encuentro:
                </strong>
            </p>
            <p>
                {{$inscripcion->punto_encuentro->responsable->nombres}}
                {{$inscripcion->punto_encuentro->responsable->apellidoPaterno}}
                <p><a href="mailto:{{ $inscripcion->punto_encuentro->responsable->mail }}" target="_blank">
                    {{ $inscripcion->punto_encuentro->responsable->mail }}
                </a></p>
                <p>{{ $inscripcion->punto_encuentro->responsable->telefonoMovil }}</p>
            </p>
        @endif
    @endif

    <p>¡Te esperamos!</p>
    TECHO - {{$inscripcion->actividad->pais->nombre}}

@endsection
