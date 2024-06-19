@extends('main')

@section('page_title')
    {{ __('frontend.last_step_waiting_for_confirmation') }}
@endsection

@section('main_image')
    <!-- <div class="techo-hero actividades">
        <h2></h2>
    </div> -->
@endsection

@section('main_content')
    <div class="container-fluid card" >
		<div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <br>
                    <h3 class="card-subtitle">
                        {{ __('frontend.you_are_pre_registered') }}
                        <a href="/actividades/{{$actividad->idActividad}}">
                            {{ $actividad->nombreActividad }}
                        </a>
                    </h3>
                    <br>
                    <p>
                        <h4>
                        {{ __('frontend.last_step_waiting_for_confirmation') }}
                        </h4>
                    </p>
                    <p>
                        {{ __('frontend.will_be_in_touch') }}
                    </p>
                    <p>
                        <h5>{{ __('frontend.coordinator') }}</h5>
                        <ul style="list-style-type:none;">
                            @foreach($actividad->coordinadores as $coordinador)
                                <li>
                                @if ($coordinador->persona->photo)
                                    <img class="imagen-perfil-mini" src="{{ '/'.$coordinador->persona->photo }}" alt="Foto">
                                @else
                                    <img src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-mini" alt="User Image"> 
                                @endif
                                    <span>
                                        {{$coordinador->persona->nombres}} {{$coordinador->persona->apellidoPaterno }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </p>
                    
                </div>
                <hr>
            </div>
            <div class="row justify-content-start">
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-4">
                            <br>
                            <p>
                                @if(Auth::check() && Auth::user()->estaPreInscripto($actividad->idActividad))
                                    <a href="{{ action('ActividadesController@show', ['id' => $actividad->idActividad]) }}" class="btn btn-link">VOLVER</a>
                                @else
                                    <a href="{{ action('InscripcionesController@puntoDeEncuentro', ['id' => $actividad->idActividad]) }}" class="btn btn-link">VOLVER</a>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
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