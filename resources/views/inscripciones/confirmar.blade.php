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
                    <h2 class="card-title">Confirmar tu inscripcion</h2>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h5 class="card-title">Punto de Encuentro</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p>Te esperamos en el siguiente lugar:</p>
                </div>
            </div>
            <form action="/inscripciones/actividad/{{$actividad->idActividad}}/gracias" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <div class="row">
                    <div class="col-md-12">
                        <p class="h4">
                            {{ $punto_encuentro->punto }}{{', '. $punto_encuentro->localidad->localidad . ', ' . $punto_encuentro->provincia->provincia }}
                            a las
                            {{ \Illuminate\Support\Carbon::parse($punto_encuentro->horario)->format('H:m') }}
                        </p>
                    </div>
                </div>
                @if($tipo->flujo == "CONSTRUCCION")
                    <div class="row">
                        <div class="col-md-12">
                            <p>Recordá que esta actividad tiene costo. Te enviaremos un email con el link de pago para
                                que puedas completar tu inscripción o puedes pagar en línea al final de esta pre-inscripción.</p>
                            <p>También puedes solicitar una BECA o un solicitar una PRORROGA, después de confirmar tu
                            pre-inscripción.</p>
                            @if ($actividad->montoMax === '0.00')
                                <h5>Donación sugerida: ${{$actividad->moneda}}  {{$actividad->montoMin}}</h5>
                            @else
                                <h5>Donación sugerida: Entre ${{$actividad->moneda}}  {{$actividad->montoMin}}
                                y ${{$actividad->moneda}}  {{$actividad->montoMax}} </h5>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p style="padding-top: 0.5em">¿Cuánto quieres donar?</p>
                        </div>
                        <div class="col-md-6">
                            <input type="number" class="form-control" placeholder="{{ $actividad->moneda }}" name="monto" required>
                        </div>
                    </div>
                @endif
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
                            Acepto los <a href="/terminos/actividades" target="_blank">Términos y condiciones.</a>
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