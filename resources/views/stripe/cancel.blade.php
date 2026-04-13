@extends('main')

@section('page_title')
    {{ __('frontend.stripe_payment_cancelled_title') }}
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="container-fluid card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="card-subtitle">{{ __('frontend.stripe_payment_cancelled_title') }}</h2>
                </div>
                <hr>
            </div>
            <div class="row justify-content-start">
                <div class="col-md-8">
                    <p>{{ __('frontend.stripe_payment_cancelled') }}</p>

                    <br>

                    @if($inscripcion && $actividad)
                        <a href="{{ action('InscripcionesController@confirmarDonacion', ['id' => $actividad->idActividad]) }}" class="btn btn-primary">
                            {{ __('frontend.go_back') }}
                        </a>
                    @else
                        <a href="/" class="btn btn-primary">{{ __('frontend.go_back') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('additional_scripts')
    <script>
        var imagenFondo = '/img/background-perfil.png';
        document.getElementById('main-background').style.backgroundImage = 'url(' + imagenFondo + ')';
        document.getElementById('main-background').style.backgroundSize = 'cover';
    </script>
@endpush

@section('footer')
    @include('partials.footer')
@endsection
