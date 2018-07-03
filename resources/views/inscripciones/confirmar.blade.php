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
                    <h5 class="card-title">Donde nos encontramos</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h5 class="card-title">Puntos de encuentro</h5>
                </div>
            </div>
            <form action="/inscripciones/actividad/{{$actividad->idActividad}}/gracias" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <div class="row">
                    <div class="col-md-12">
                        {{ $punto_encuentro->punto }}
                    </div>
                    <div class="col-md-12">
                        {{ $punto_encuentro->horario }}
                    </div>
                </div>
                @if($tipo->flujo == "CONSTRUCCION")
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="card-title">Costo de la contrucción: ${{$actividad->costo}} Pesos</h5>
                            <p>Recordá que esta actividad tiene costo. Te enviaremos un email con el link de pago para que puedas completar tu inscripción!</p>
                        </div>
                    </div>
                @endif
                <div class="row  align-middle">
                    <div class="col-md-3 text-primary"><i class="fas fa-long-arrow-alt-left "></i><a
                                href="/inscripciones/actividad/{{$actividad->idActividad}}"> Volver</a></div>
                    <div class="col-md-3"><input type="submit" value="CONFIRMAR" class="btn btn-primary"></div>
                </div>
            </form>
        </div>
        <div class="col-md-4">
            <div class="card">
                <img src="https://placeholdit.co/i/555x150?bg=d3d3d3">
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
                        <span>{{ $actividad->fechaInicio->format('d-m-Y')}}</span></div>
                    <div class="col-md-4"><i class="far fa-clock"></i>
                        <span>{{ $actividad->fechaInicio->format('h:m')}}</span></div>
                    <div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>{{ $actividad->lugar }}</span>
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