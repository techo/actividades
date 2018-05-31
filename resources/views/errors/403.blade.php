@extends('main')

@section('main_content')
    <div class="">
        <div class="row d-flex justify-content-center">
            <h1 class="text-primary">Oops! Vuelve y busca otra ruta</h1>
        </div>
        <div class="row d-flex justify-content-center">
            <img src="/img/404.png" alt="404">
        </div>
        <div class="row d-flex justify-content-center">
            <button class="btn btn-primary btn-lg" onclick="window.history.go(-1); return false;"><i class="fas fa-arrow-circle-left"></i> ATRÁS</button>
        </div>
    </div>
@endsection