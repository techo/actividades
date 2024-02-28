@extends('main')

@section('page_title')
    {{ __('frontend.meeting_points') }}
@endsection


@section('main_image')
@endsection

@section('main_content')
    <div class="container-fluid card" >
        <div class="card-body">
            <inscripcion id="{{$actividad->idActividad}}" csrf_token="{{ csrf_token() }}"></inscripcion>
        </div>
    </div>
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


@section('footer')
    @include('partials.footer')
@endsection