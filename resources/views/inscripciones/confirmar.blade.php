@extends('main')

@section('page_title')
    Detalle de Actividad
@endsection


@section('main_image')
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    @if( $actividad->confirmar == 1 ||
                    $actividad->pago == 1 )
                        <h2 class="card-title">Confirmar tu pre-inscripción</h2>
                    @else
                        <h2 class="card-title">Confirmar tu inscripción</h2>
                    @endif
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h4>Te esperamos en el siguiente lugar:</h4>
                </div>
            </div>
            <form action="/inscripciones/actividad/{{$actividad->idActividad}}/gracias" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            @if ($punto_encuentro->localidad)
                                {{ $punto_encuentro->punto }}{{', '. $punto_encuentro->localidad->localidad . ', ' . $punto_encuentro->provincia->provincia }}
                            @else
                                {{ $punto_encuentro->punto }}{{', ' . $punto_encuentro->provincia->provincia }}
                            @endif
                        </p>
                        <p>
                            {{ \Illuminate\Support\Carbon::parse($punto_encuentro->horario)->format('H:i') }} hs
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p class="h4">
                            Coordinador:
                        </p>
                        <p>{{ $punto_encuentro->responsable->nombreCompleto }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <label for="aceptar_terminos">
                            <input
                                    type="checkbox"
                                    name="aceptar_terminos"
                                    id="aceptar_terminos"
                                    value="1"
                                    required
                            >
                            Acepto la <a href="/carta-voluntariado" target="_blank">carta de voluntariado</a>
                        </label>
                        @if($mensaje = Session::get('status'))
                            <p class="text-danger">{{ $mensaje }}</p>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row  align-middle">
                    <div class="col-md-2 text-primary">
                        <a href="/inscripciones/actividad/{{$actividad->idActividad}}" class="btn btn-link"> Volver</a>
                    </div>
                    <div class="col-md-3">
                        <input type="submit" value="CONFIRMAR" class="btn btn-primary">
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-4 prev">
            <div class="card d-none d-lg-block" style="border: none">
                <img src="{{ $actividad->tipo->imagen }}" style="margin-bottom: 1em;">
                <div class="row">
                    <div class="col-md-12" >
                        <h6 style="color: {{$actividad->tipo->categoria->color}}; font-weight: 700 !important;" >{{ $actividad->tipo->nombre }}</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5>{{ $actividad->nombreActividad }}</h5>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4"><i class="far fa-calendar"></i>
                        <span>{{ $actividad->fechaInicio->format('d/m/y') }}</span></div>
                    <div class="col-md-4"><i class="far fa-clock"></i>
                        <span>{{ $actividad->fechaInicio->format('h:m') }}</span></div>
                    <div class="col-md-4"><i class="fas fa-map-marker-alt"></i> 
                        <span>
                            @if ($actividad->idLocalidad)
                                {{ $actividad->localidad->localidad }}, {{ $actividad->provincia->provincia }}
                            @else
                                {{ $actividad->provincia->provincia }}
                            @endif
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        {!! $actividad->descripcion !!}
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
@section('footer')
    @include('partials.footer')
@endsection