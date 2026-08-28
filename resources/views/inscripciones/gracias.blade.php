@extends('main')

@section('page_title')
    {{ __('frontend.activity_detail') }}
@endsection


@section('main_image')
    <!-- <div class="techo-hero actividades">
        <h2></h2>
    </div> -->
@endsection

@section('main_content')
    <div class="container-fluid card" >
        <div class="card-body">

            @include('partials.inscripcion-breadcrumb', ['flowSteps' => $flowSteps ?? []])

            <div class="text-center py-3">
                <i class="fa fa-check-circle text-success mb-3" style="font-size:3.5rem;"></i>
                <h2 class="card-subtitle mb-1">{{ __('frontend.inscription_confirmed') }}</h2>
                <p class="text-muted mb-2">{{ __('frontend.already_inscripted') }}</p>
                <h3 class="card-title font-weight-bold mb-3">
                    <a href="/actividades/{{$actividad->idActividad}}" class="link">{{ $actividad->nombreActividad }}</a>
                </h3>

                <div class="d-flex flex-wrap justify-content-center text-muted mb-2">
                    @if($actividad->show_dates)
                        <span class="mx-2 my-1"><i class="far fa-calendar mr-1"></i> {{ $actividad->fechaInicio->format('d/m/Y') }}</span>
                        <span class="mx-2 my-1"><i class="far fa-clock mr-1"></i> {{ $actividad->fechaInicio->format('H:i') }}hs</span>
                    @endif
                    @if($actividad->show_location)
                        <span class="mx-2 my-1">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            @if(!isset($actividad->localidad) || optional($actividad->localidad)->localidad == "No definida")
                                {{ optional($actividad->provincia)->provincia }}, {{ optional($actividad->pais)->nombre }}
                            @else
                                {{ $actividad->localidad->localidad }}, {{ optional($actividad->provincia)->provincia }}
                            @endif
                        </span>
                    @endif
                </div>
            </div>

            @if(!empty($exentoPorSocio))
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-success d-flex align-items-start mb-4" style="border-radius:10px;">
                            <i class="fa fa-heart fa-2x mr-3 mt-1 text-success flex-shrink-0"></i>
                            <div>
                                <strong>{{ __('frontend.socio_exento_titulo') }}</strong>
                                <p class="mb-0 mt-1" style="font-size:.9rem;">{{ __('frontend.socio_exento_texto') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <p class="text-muted">
                        {{ __('frontend.mail_message') }} <a href="/perfil/actividades">{{ __('frontend.my_activities') }}</a>.
                    </p>
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