@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        Hola {{$inscripcion->persona->nombres}}
    </p>
    <p>Ya estás confirmado para participar en
        <h3>{{$inscripcion->actividad->nombreActividad}}</h3>
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
            </p>
        @endif
    @endif

    <p>¡Te esperamos!</p>
    TECHO - {{$inscripcion->actividad->pais->nombre}}

@endsection
