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
            <h1 class="card-subtitle">¡Inscripción confirmada!</h1>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-8">
            <h3 class="card-title">
                <br>
                Ya estás inscripto a
                <a href="/actividades/{{$actividad->idActividad}}">
                    {{ $actividad->nombreActividad }}
                </a>
            </h3>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-8">
            <p>
                Te enviamos un mail con más información sobre esta actividad.
                Entra al panel de usuario para ver las actividades a las que estás inscripto y modificarlas.
            </p>
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