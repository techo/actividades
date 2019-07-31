@extends('main')

@section('page_title')
    Esperá a que te confirmen
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="card-subtitle">¡Quedás a la espera de que te confirmemos!</h1>

            <p>
                En breve nos contactaremos con vos por mail para comunicarte si se aprueba tu inscripción.
            </p>
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
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection