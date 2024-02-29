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

@endsection

@section('main_content')
<div class="row justify-content-center h-auto">
        <div class="col-md-3 d-none d-md-block">
            <!-- <img src="/img/techo_logo_big.png" alt="Techo" width="80%"> -->
        </div>
        <div class="col-md-7">
            <div class="list-group">
                @foreach ($paises as $pais)
                    <a href="{{$pais->abreviacion}}" class="list-group-item list-group-item-action bg-secondary-blue list-group-pais">{{$pais->nombre}}</a>
                @endforeach
                
            </div>
        </div>
    </div>

@endsection


@push('additional_scripts')
    <script>
        // Define la URL de la imagen de fondo
        var imagenFondo = '/img/background-paises.png';
        // Selecciona el elemento con el ID "main-background" y establece la imagen de fondo
        document.getElementById('main-background').style.backgroundImage = 'url(' + imagenFondo + ')';
    </script>
@endpush

@section('footer')
    @include('partials.footer')
@endsection
