@extends('main')

@section('page_title')
    {{ __('frontend.inscription_confirmed') }}
@endsection


@section('main_image')
@endsection

@section('main_content')
    <div class="container-fluid card">
        <div class="card-body">
            <div class="text-center py-3">
                <i class="fa fa-check-circle text-success mb-3" style="font-size:3.5rem;"></i>
                <h2 class="card-subtitle mb-1">{{ __('frontend.inscription_confirmed') }}</h2>
                <p class="text-muted mb-2">{{ __('frontend.with_this_donation') }}</p>
                <h3 class="card-title font-weight-bold mb-3">
                    <a href="/actividades/{{$actividad->idActividad}}" class="link">{{ $actividad->nombreActividad }}</a>
                </h3>
                <div class="d-flex flex-wrap justify-content-center text-muted mb-2">
                    <span class="mx-2 my-1"><i class="far fa-calendar mr-1"></i> {{ $actividad->fechaInicio->format('d/m/Y') }}</span>
                    <span class="mx-2 my-1"><i class="far fa-clock mr-1"></i> {{ $actividad->fechaInicio->format('H:i') }}hs</span>
                    <span class="mx-2 my-1">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ optional($actividad->localidad)->localidad }}, {{ optional($actividad->provincia)->provincia }}
                    </span>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <p class="text-muted text-center">{{ __('frontend.important_remainder') }}</p>
                    <div class="border p-3 mb-3" style="border-radius:10px;">
                        <p class="mb-1">
                            <strong>{{ __('frontend.meeting_points') }}:</strong>
                            {{ $inscripcion->punto_encuentro->punto }}, {{ \Carbon\Carbon::parse($inscripcion->punto_encuentro->horario)->format('H:i') }} hs
                        </p>
                        @if(optional($inscripcion->punto_encuentro)->responsable)
                            <p class="mb-0">
                                <strong>{{ __('frontend.coordinator') }}</strong>
                                {{ $inscripcion->punto_encuentro->responsable->nombreCompleto }}
                                (<a href="mailto:{{$inscripcion->punto_encuentro->responsable->mail}}">{{$inscripcion->punto_encuentro->responsable->mail}}</a>)
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center flex-wrap mt-3">
                <button type="button" class="btn btn-primary rounded-pill px-4 mx-2 my-1" data-toggle="modal" data-target="#compartirModal">
                    <i class="fas fa-share-alt mr-1"></i> {{ __('frontend.share') }}
                </button>
                <a href="/actividades/{{ $actividad->idActividad }}" class="btn btn-link mx-2 my-1">{{ __('frontend.go_back') }}</a>
            </div>
        </div>
    </div>
@endsection

@push('additional_scripts')
    <script>
        // Define la URL de la imagen de fondo
        var imagenFondo = '/img/background-perfil.png';
        // Selecciona el elemento con el ID "main-background" y establece la imagen de fondo
        document.getElementById('main-background').style.backgroundImage = 'url(' + imagenFondo + ')';
        document.getElementById('main-background').style.backgroundSize = 'cover';
    </script>
@endpush


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