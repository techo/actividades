@extends('main')

@section('page_title')
    ¡Pago fuera de fecha!
@endsection

@section('main_image')
    <div class="techo-hero actividades">
    <!-- <img src="{{ asset('/img/hero-slim.jpg') }}" alt="hero image" height="210"> -->
        <h2 class="text-uppercase">Si te da lo mismo, estás haciendo mal las cuentas <br>
            Anotate y participá</h2>
    </div>
@endsection

@section('main_content')
<div class="row d-flex justify-content-center">
    <span >

    <img class="text-center" src="/img/Failed-Payment-300x300.png" alt="Error al procesar el pago" width="200" height="200" align="center">
    </span>
</div> <p> </p>
<div class="row d-flex justify-content-center">
        <span class="align-middle">
        <p class="h3">¡Pago fuera de fecha!</p>
        <p>El pago se hizo fuera de la fecha límite. No estás confirmado para participar, te enviamos un mail con instrucciones por si querés recuperar tu donación.</p>
    </span>

</div> <p> </p>
    <div class="row d-flex justify-content-center">

        <div class="col-md-4 col-lg-offset-4">
        <p> <a href="/" class="btn btn-link">VOLVER AL HOME</a></p>
        </div>
    </div>
@endsection

@section('additional_scripts')

@endsection
