@extends('main')

@section('page_title')
    Confirma con tu pago
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="card-subtitle">¡Listo para pagar por la plataforma!</h1>
        </div>
        <hr>
    </div>
    <br>
    <br>
    <div class="row justify-content-start">
        <div class="col-md-9">

            @if($actividad->pago == 1)

                <p><strong>Elegiste donar: ${{ $actividad->moneda }} {{ $payment->getMonto() }}</strong></p>
                <div class="row justify-content-start">
                    <div class="col-md-6 col-sm-6">
                        @php
                            $config = json_decode($actividad->pais->config_pago);
                            $form = strtolower($config->payment_class)
                        @endphp
                        @include('pagos.' . $form)
                        <span class="text-muted techo-small-text" style="margin-top: -0.5em">Al hacer click se te redirigirá a la plataforma de pago</span>
                    </div>
                </div>
            @endif

            <br><br>
                <div class="row">
                    <div class="col-md-4">
                        <br><br>
                        <p>
                            <a href="{{ action('InscripcionesController@confirmarDonacion', ['id' => $actividad->idActividad]) }}" class="btn btn-link">VOLVER</a>
                        </p>
                    </div>
                </div>
        </div>
    </div>

@endsection

@section('footer')
    @include('partials.footer')
@endsection