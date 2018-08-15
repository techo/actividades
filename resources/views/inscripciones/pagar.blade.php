@extends('main')

@section('page_title')
    Confirma con tu pago
@endsection


@section('main_image')
    <div class="techo-hero">
        <img src="/img/hero-slim.jpg" alt="hero image" height="210">
        <h2>Inscríbete y acompáñanos con tu voluntariado</h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="card-subtitle">¡Sólo queda un paso! Confirmar <br>
                la participación con tu donación</h1>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-9">
            <h3 class="card-title">
                <br>
                Ya estás pre-inscripto a
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
            <p>
                Te hemos enviado por mail toda la información pertinente para la actividad.  Puedes ingresar al panel de
                usuario para visualizar o modificar tu presencia.
            </p>
            <h3>
                Para realizar tu donación
            </h3>
            <p>
                Vas a estar recibiendo por mail un link con instrucciones para que puedas donar con la plataforma de pagos en otro momento.
                Si quieres, puedes realizar tu donativo con el botón de aquí abajo, o puedes solicitar una beca.
            </p>
            <p><strong>Donarás ${{ $actividad->moneda }} {{ $payment->monto }}</strong></p>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-6 col-sm-6">
            @php
                $config = json_decode($actividad->pais->config_pago);
                $form = strtolower($config->payment_class)
            @endphp
            @include('pagos.' . $form)
            <span class="text-muted techo-small-text" style ="margin-top: -0.5em">Al hacer clic se te redirigirá a la plataforma de pago</span>
        </div>
        <div class="col-sm-6 col-md-6">
            <a href="{{ $actividad->beca }}" class="btn btn-link" target="_blank">SOLICITAR BECA</a>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-4">
            <p>
                <a href="/" class="btn btn-link">VOLVER AL HOME</a>
            </p>
        </div>
        <div class="col-md-4">

        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection