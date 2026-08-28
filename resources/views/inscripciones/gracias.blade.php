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

            <div class="row">
                <div class="col-md-12">
                    <h2 class="card-subtitle"> {{ __('frontend.inscription_confirmed') }}</h2>
                </div>
                <hr>
            </div>
            @if(!empty($exentoPorSocio))
                <div class="row">
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
            <div class="row">
                <div class="col-md-8">
                    <h3 class="card-title">
                        {{ __('frontend.already_inscripted') }}
                        <a href="/actividades/{{$actividad->idActividad}}" class="link">
                            {{ $actividad->nombreActividad }}
                        </a>
                    </h3>
                </div>
            </div>
            <div class="row justify-content-start">
                <div class="col-md-8">
                    <p>
                        {{ __('frontend.mail_message') }} <a href="/perfil/actividades">{{ __('frontend.my_activities') }}</a>.
                    </p>
                </div>
            </div>
            <div class="row justify-content-start">
                <div class="col-md-2">
                    <button type="button" class="btn btn-link" data-toggle="modal" data-target="#compartirModal">
                        <i class="fas fa-share-alt"></i>  {{ __('frontend.share') }}
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="/" class="btn btn-link">{{ __('frontend.go_back') }}</a>
                </div>
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