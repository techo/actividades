@extends('main')

@section('page_title')
    {{ __('frontend.my_activities') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
    <h1>{{ __('frontend.my_activities') }}</h1>
    <div class="col-md-6">
        <h3>{{ __('frontend.next_activities') }} </h3>
    </div>

    <mis-actividades></mis-actividades>
    <div class="col-md-6">
        <h3>{{ __('frontend.past_activities') }} </h3>
    </div>
    <mis-actividades date="pasadas"></mis-actividades>
    <p>&nbsp;</p>
@endsection

@push('additional_scripts')
    <script>
        // Define la URL de la imagen de fondo
        var imagenFondo = '/img/background-actividades.png';
        // Selecciona el elemento con el ID "main-background" y establece la imagen de fondo
        document.getElementById('main-background').style.backgroundImage = 'url(' + imagenFondo + ')';
        document.getElementById('main-background').style.backgroundSize = 'contain';
    </script>
@endpush

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
