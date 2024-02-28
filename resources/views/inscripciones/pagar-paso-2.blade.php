@extends('main')

@section('page_title')
    {{ __('frontend.confirm_by_paying') }}
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="card-subtitle">{{ __('frontend.ready_for_paying') }}</h1>
        </div>
        <hr>
    </div>
    <br>
    <br>
    <div class="row justify-content-start">
        <div class="col-md-9">

            @if($actividad->pago == 1)

                <p><strong>{{ __('frontend.you_choose') }} ${{ $actividad->moneda }} {{ $payment->getMonto() }}</strong></p>
                <div class="row justify-content-start">
                    <div class="col-md-6 col-sm-6">
                        @php
                            $config = json_decode($actividad->pais->config_pago);
                            $form = strtolower($config->payment_class)
                        @endphp
                        @include('pagos.' . $form)
                        <span class="text-muted techo-small-text" style="margin-top: -0.5em">{{ __('frontend.redirect_pay_platform') }}</span>
                    </div>
                </div>
            @endif

            <br><br>
                <div class="row">
                    <div class="col-md-4">
                        <br><br>
                        <p>
                            <a href="{{ action('InscripcionesController@confirmarDonacion', ['id' => $actividad->idActividad]) }}" class="btn btn-link">{{ __('frontend.go_back') }}</a>
                        </p>
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