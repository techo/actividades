@extends('main')

@section('page_title')
    {{ __('frontend.profile') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
	<perfil usuario="{{json_encode($usuario)}}"></perfil>
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
        document.getElementById('main-background').style.backgroundSize = 'contain';
    </script>
@endpush