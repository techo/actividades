@extends('main')

@section('page_title')
    TECHO: {{ __('frontend.welcome') }}
@endsection

@section('main_image')
    @if ($mensaje = Session::get('mensaje'))
    <div class="alert alert-success alert-block" style="margin-top: 1rem;">
        <button type="button" class="close" data-dismiss="alert">×</button> 
            <strong>{{ $mensaje }}</strong>
    </div>
    @endif


    <div style="background-image: url('/img/background-home.png'); background-size: cover;" class="row justify-content-center align-items-center py-4 px-4 h-auto">
        <div class="col-md-3 d-none d-md-block">
            <!-- <img src="/img/techo_logo_big.png" alt="Techo" width="80%"> -->
        </div>
        <div class="col-md-3">
            <div class="list-group">
                @foreach ($paises as $pais)
                    <a href="{{$pais->abreviacion}}" class="list-group-item list-group-item-action bg-secondary-blue list-group-pais">{{$pais->nombre}}</a>
                @endforeach
                
            </div>
        </div>
    </div>

    

@endsection

@section('main_content')
    

@endsection
