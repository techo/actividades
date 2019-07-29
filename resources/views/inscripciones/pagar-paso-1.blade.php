@extends('main')

@section('page_title')
    Confirma con tu pago
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="card-subtitle">¡Sólo queda un paso! Confirmar <br>
                la participación con tu donación</h1>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-9">
            <h3 class="card-title">
                <br>
                Estás pre-inscripto a
                <a href="/actividades/{{$actividad->idActividad}}">
                    {{ $actividad->nombreActividad }}
                </a>
            </h3>
            <blockquote class="blockquote">
                {{ $actividad->mensajeInscripcion }}
            </blockquote>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-9">
            <p>
                Te hemos enviado por mail toda la información pertinente para la actividad.  Puedes ingresar al panel de
                usuario para visualizar o modificar tu presencia.
            </p>
            <h3>
                Para realizar tu donación
            </h3>
            <p>
                @if(!empty($actividad->beca))
                Vas a estar recibiendo por mail un link con instrucciones para que puedas donar con la plataforma de pagos en otro momento.
                Si quieres, puedes realizar tu donativo con el botón de aquí abajo, o puedes solicitar una beca.
                @else
                    Vas a estar recibiendo por mail un link con instrucciones para que puedas donar con la plataforma de pagos en otro momento.
                    Si quieres, puedes realizar tu donativo con el botón de aquí abajo.
                @endif
            </p>

            @if($actividad->pago == 1)

                @if ($actividad->montoMax === '0.00')
                    <h5>Donación sugerida: {{$actividad->montoMin}} ({{$actividad->moneda}}$)</h5>
                @else
                    <h5>Donación sugerida: Entre {{$actividad->montoMin}}
                        y {{$actividad->montoMax}} ({{$actividad->moneda}}$)</h5>
                @endif
                    <form action="{{ action('InscripcionesController@donacionCheckout', ['id' => $actividad->idActividad]) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="font-weight-bold">MONTO A DONAR</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="number" class="form-control" placeholder="{{ $actividad->moneda }}" name="monto"
                                               min="1" required step="0.1">
                                    </div>
                                </div>
                            </div>
                            @if(!empty($actividad->beca))
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="font-weight-bold"> &nbsp;o también puedes</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="{{ $actividad->beca }}" class="btn btn-link" target="_blank">SOLICITAR UNA BECA</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <br><br>
                        <div class="row">
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">SIGUIENTE</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <br><br>
                                <p>
                                    @if(Auth::check() && Auth::user()->estaPreInscripto($actividad->idActividad))
                                        <a href="{{ action('ActividadesController@show', ['id' => $actividad->idActividad]) }}" class="btn btn-link">VOLVER</a>
                                    @else
                                        <a href="{{ action('InscripcionesController@puntoDeEncuentro', ['id' => $actividad->idActividad]) }}" class="btn btn-link">VOLVER</a>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </form>
            @endif
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection