@extends('main')

@section('page_title')
    Home
@endsection

@section('main_image')
    <div class="techo-hero">
        <img src="/img/hero.jpg" alt="hero image">
        <h1>Tu ayuda comienza acá</h1>
    </div>
@endsection

@section('main_content')
    <h1 class="mt-1 techo-h1">¿En qué actividad quieres participar?</h1>
    <div class="row">
        @for ($i = 1; $i <= 3; $i++)
        <div class="col-md-4">
            <div class="card">
                <img class="card-img-top" src="/img/tarjeta-1.jpg" alt="Card image cap" >
                <div class="card-body px-0" style="overflow-x: scroll; ">
                    <h5 class="card-title">Actividades en Asentamientos</h5>
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    <p><a href="#"  class="techo-h6 techo-blue">Actividades a Realizar <i style="padding-top: 0.5em" data-feather="chevron-down"></i></a></p>
                    <a href="#" class="btn techo-btn-azul">Quiero Participar</a>
                </div>
            </div>
        </div>
        @endfor
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection