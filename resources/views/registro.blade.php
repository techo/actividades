@extends('main')

@section('page_title')
    {{ __('frontend.register') }}
@endsection

@section('main_image')
@endsection

@push('additional_scripts')
    <script>
        // Define la URL de la imagen de fondo
        var imagenFondo = '/img/background-actividades.png';
        // Selecciona el elemento con el ID "main-background" y establece la imagen de fondo
        document.getElementById('main-background').style.backgroundImage = 'url(' + imagenFondo + ')';
        document.getElementById('main-background').style.backgroundSize = 'cover';
    </script>
@endpush

@section('main_content')
	@if(isset($persona))
		<registro
				nombre="{{$persona->nombre}}"
                apellido="{{$persona->apellido}}"
                genero="{{$persona->genero}}"
                email="{{$persona->email}}"
                facebook_id="{{$persona->facebook_id}}"
                google_id="{{$persona->google_id}}"
                linkear={{isset($linkear)?$linkear:''}}
        ></registro>
	@else
        @if(isset($mensaje))
            <div class="alert alert-danger">
                <strong>{{$mensaje}}</strong>
            </div>
        @endif
		<registro></registro>
	@endif
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
