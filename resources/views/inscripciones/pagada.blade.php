@extends('main')

@section('page_title')
    Detalle de Actividad
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="card-subtitle">¡Participación confirmada!</h1>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-8">
            <h3 class="card-title">
                <br>
                Con esta donación, ya confirmaste tu participación en
                <a href="/actividades/{{$actividad->idActividad}}">
                    {{ $actividad->nombreActividad }}
                </a>
            </h3>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-8">
            <p>
                Te recordamos algunos datos importantes:
            </p>
            <p>
                <strong>Punto de Encuentro: </strong><br>
                {{ $inscripcion->punto_encuentro->punto }}, {{ \Carbon\Carbon::parse($inscripcion->punto_encuentro->horario)->format('H:i') }} hs
            </p>
            <p>Este punto está coordinado por {{ $inscripcion->punto_encuentro->responsable->nombreCompleto }}
                (<a href="mailto:{{$inscripcion->punto_encuentro->responsable->mail}}">
                    {{$inscripcion->punto_encuentro->responsable->mail}}</a>), podés
                comunicarte si tenés alguna duda o pregunta sobre el punto de encuentro.
            </p>
            <p>La actividad se realizará en {{ $actividad->localidad->localidad }},
                {{ $actividad->provincia->provincia }},
                {{ $actividad->pais->nombre }}
            </p>
            <p>Comienzo: {{ $actividad->fechaInicio->format('d/m/Y H:i') }}</p>
            <p>Fin: {{ $actividad->fechaFin->format('d/m/Y H:i') }}</p>
            <p class="h3">¡Te esperamos!</p>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-2">
            <button type="button" class="btn btn-link" data-toggle="modal" data-target="#compartirModal">
                <i class="fas fa-share-alt"></i>  COMPARTIR
            </button>
        </div>
        <div class="col-md-2">
            <a href="/" class="btn btn-link">Volver al Inicio</a>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('aditional_html')
    @include(
        'partials.compartir-modal',
        ['url' => action('ActividadesController@show', ['id' => $actividad->idActividad]),
        'title' => $actividad->nombreActividad]
    )
@endsection