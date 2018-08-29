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
                    @if(strtoupper($actividad->tipo->flujo) == 'CONSTRUCCION')
                        <h2 class="card-title">Confirmar tu pre-inscripcion</h2>
                    @else
                        <h2 class="card-title">Confirmar tu inscripcion</h2>
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
                            {{ $punto_encuentro->punto }}{{', '. $punto_encuentro->localidad->localidad . ', ' . $punto_encuentro->provincia->provincia }}
                        </p>
                        <p>
                            {{ \Illuminate\Support\Carbon::parse($punto_encuentro->horario)->format('H:m') }} hs
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p class="h4">
                            Coordinador:
                        </p>
                        <p>{{ $punto_encuentro->responsable->nombreCompleto }}</p>
                        <p>{{ $punto_encuentro->responsable->telefonoMovil }}</p>
                        <p>{{ $punto_encuentro->responsable->mail }}</p>
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
                            Acepto la <a href="/terminos/actividades" target="_blank">carta de voluntariado</a>
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
        <div class="col-md-4">
            <div class="card" style="border: none">
                <img src="{{ $actividad->tipo->imagen }}" style="margin-bottom: 1em;">
                <div class="row">
                    <div class="col-md-12">
                        <h6>{{ $actividad->tipo->nombre }}</h6>
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
                    <div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>{{ $actividad->localidad->localidad }}</span>
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