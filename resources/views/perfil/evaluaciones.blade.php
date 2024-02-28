@extends('main')

@section('page_title')
    {{ __('frontend.my_score') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
    <h1>{{ __('frontend.my_score') }}</h1>


    @if ( $promedioTecnico > 4) 
    	<evaluacion-personal :puntaje-tecnico="{{ round($promedioTecnico,1)}}" :puntaje-social="{{ round($promedioSocial,1) }}" :puntaje-genero="{{ round($promedioGenero,1)}}" ></evaluacion-personal>
    @else
    	<p> {{ __('frontend.my_score_not_active') }}<p>
    @endif
    <br>
@endsection

@section('footer')
    @include('partials.footer')
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

@section('additional_scripts')
@endsection
