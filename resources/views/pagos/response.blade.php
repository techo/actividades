@extends('main')

@section('page_title')
    Confirmación del pago
@endsection

@section('main_image')
    <div class="techo-hero actividades">
    <!-- <img src="{{ asset('/img/hero-slim.jpg') }}" alt="hero image" height="210"> -->
        <h2 class="text-uppercase">Si te da lo mismo, estás haciendo mal las cuentas <br>
            Anotate y participá</h2>
    </div>
@endsection

@section('main_content')
    <h3>Hubo un problema</h3>
    <p>Hubo un problema con el pago:</p>
    <p>{{ $payment->message }}</p>
@endsection

@section('additional_scripts')

@endsection
