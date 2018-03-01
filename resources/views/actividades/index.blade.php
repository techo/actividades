@extends('main')

@section('page_title')
    Listado de Actividades
@endsection

@section('main_image')
    <div class="techo-hero">
        <img src="/img/hero-slim.jpg" alt="hero image" height="210">
        <h2>Inscríbete y acompáñanos con tu voluntariado</h2>
    </div>
@endsection

@section('main_content')
    <h1 class="mt-1 techo-h1">Actividades en Asentamientos</h1>
    <div class="row">
        @for ($i = 1; $i <= 3; $i++)
        <div class="col-md-4">
            <div class="card" style="cursor: pointer;">
                <img class="card-img-top" src="/img/tarjeta-1.jpg" alt="Card image cap" >
                <div class="card-body px-0" style="overflow-x: scroll; ">
                    <p class="techo-titulo-card">Actividades en Asentamientos</p>
                    <h5 class="card-title">Detección en Barrio Los Cedros</h5>
                    <div style="width: 100%; border-top: #b7babf thin solid;border-bottom: #b7babf thin solid; font-size: 14px; margin: 0.5em 0; padding: 0.5em 0">
                        <span class="col-sm-4"><i data-feather="calendar"></i> <span style="padding-bottom: 5px">27/02</span></span>
                        <span class="col-sm-4"><i data-feather="clock"></i> 9:00am</span>
                        <span class="col-sm-4"><i data-feather="map-pin"></i> Vicente López</span>
                    </div>
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                </div>
            </div>
        </div>
        @endfor
    </div>
@endsection