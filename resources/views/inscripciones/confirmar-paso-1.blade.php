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
            <br>
            <h3 class="card-subtitle">
                Estás pre-inscripto a
                <a href="/actividades/{{$actividad->idActividad}}">
                    {{ $actividad->nombreActividad }}
                </a>
            </h3>
            <br>
            <p>
                <h4>
                ¡Quedás a la espera de que te confirmemos!
                </h4>
            </p>
            <p>
                En breve nos contactaremos con vos para comunicarte si se aprueba tu inscripción. Cualquier consulta contactá al coodinador.
            </p>
            <p>
                <h5>Coordinador</h5>
                <p>{{ $actividad->coordinador->nombres  }} {{ $actividad->coordinador->apellidoPaterno }}</p>
                <p>{{ $actividad->coordinador->mail  }}</p>
            </p>
            
        </div>
        <hr>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-4">
                    <br>
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