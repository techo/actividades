@extends('main')

@section('page_title')
    {{ $actividad->tipo->nombre }}:  {{ $actividad->nombreActividad }}
@endsection



@section('main_image')
    <!-- <div class="techo-hero actividades">
        {{--<img src="/img/hero-slim.jpg" alt="hero image">--}}
        <h2></h2>
    </div> -->
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

@section('main_content')
    <div class="card" >
		<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<h6 class="card-subtitle text-uppercase font-weight-bold" style="color:{{$actividad->tipo->categoria->color}}">{{ $actividad->tipo->nombre }}</h6>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2 class="card-title">{{ $actividad->nombreActividad }}</h2>
			</div>
		</div>
		<div class="row justify-content-start">
            @if($actividad->show_dates)
                <div class="col-md-2"><i class="far fa-calendar"></i> <span>{{ $actividad->fechaInicio->format('d/m/Y')}}</span></div>
                <div class="col-md-2"><i class="far fa-clock"></i> <span>{{ $actividad->fechaInicio->format('H:i')}}hs</span></div>
            @endif
            @if($actividad->show_location)
                <div class="col-md-8">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>
                        @if (!isset($actividad->localidad) || $actividad->localidad->localidad == "No definida")
                            {{ $actividad->provincia->provincia }}, {{ $actividad->pais->nombre }}
                        @elseif (!isset($actividad->provincia))
                            {{ __('backend.unspecified') }}
                        @else
                            {{ $actividad->localidad->localidad }}, {{ $actividad->provincia->provincia }}
                        @endif
                    </span>
                </div>
            @endif
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h5> {{ __('frontend.description') }}</h5>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				{!! $actividad->descripcion !!}
			</div>
		</div>

		<hr>
		<div class="row">
			<div class="col-md-12">
                <h5>{{ __('frontend.coordinator') }}</h5>
			</div>
		</div>
        <div class="row">
            <div class="col-md-12 p-1 ">
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
                                @if (strpos($coordinador->persona->telefonoMovil, '+') === 0 && strlen($coordinador->persona->telefonoMovil) >= 7 && $coordinador->activaWhatsapp)
                                    <a href="https://wa.me/{{ $coordinador->persona->telefonoMovil }}" target="_blank"><i class="fa fa-whatsapp text-success" aria-hidden="true"></i></a>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
                
            </div>
        </div>
		<hr>
        @if ($actividad->show_location)
            <div  class="row">
                <div class="col-md-12">
                    <h5>{{ __('frontend.meeting_points') }}</h5>
                </div>
            </div>
        
            @foreach($actividad->puntosEncuentro as $puntoEncuentro)
                @if($puntoEncuentro->estado)
                    <div class="row">
                        <div class="col-md-4">
                            {{$puntoEncuentro->punto}}
                        </div>
                        <div class="col-md-4">
                            @php
                                echo isset($puntoEncuentro->localidad->localidad) ? $puntoEncuentro->localidad->localidad . ', ': '';
                                echo isset($puntoEncuentro->provincia->provincia) ? $puntoEncuentro->provincia->provincia . '': '';                    @endphp

                        </div>
                        <div class="col-md-4">
                            <strong>{{ __('frontend.referring') }}:</strong>
                                @if ($puntoEncuentro->responsable)
                                    @if ($puntoEncuentro->responsable->photo)
                                        <img class="imagen-perfil-mini" src="{{ '/'.$puntoEncuentro->responsable->photo }}" alt="Foto">
                                    @else
                                        <img src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-mini" alt="User Image"> 
                                    @endif
                                    {{ $puntoEncuentro->responsable->nombreCompleto }}
                                @else
                                    {{  __('frontend.not_defined') }}
                                @endif

                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
        </div>
    </div>

@endsection

@section('footer')
<footer class="footer inscripcion-bar fixed-bottom">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="h5">{{ $actividad->nombreActividad }}</p>
            </div>
            <div class="col-md-6">
                <div style="text-align: right">
                    @if ($chatGrupalWhatsapp && $actividad->chat_grupal_whatsapp != null)
                        <a class="btn rounded-pill text-white bg-success" href="{{ $actividad->chat_grupal_whatsapp }}" target="_blank">
                            <i class="fa fa-whatsapp fa-lg" aria-hidden="true"></i>
                            <span>{{ __('frontend.group_chat') }}</span>
                        </a>
                    @endif
                    <a class="btn btn-link" data-toggle="modal" data-target="#compartirModal">
                        <i class="fas fa-share-alt"></i>{{ __('frontend.share') }}
                    </a>
                    <a 
                        class="btn {{ $clase }}"
                        href="{{ $accion }}"
                        @if (!$habilitado) 
                            disabled
                        @endif
                        >
                        <strong>{{ $mensaje }}<strong>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>


@endsection

@section('aditional_html')
	@include('partials.compartir-modal', ['url' => Request::url(), 'title' => $actividad->nombreActividad])

    
@endsection

@push('additional_scripts')

    @if(!empty($actividad->seguimiento_google))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $actividad->seguimiento_google }}"></script> 
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{ $actividad->seguimiento_google }}');
        </script>
    @endif

	<script>
        function mostrarTooltip(){
            $("#copiar_url").tooltip({trigger: 'manual'});
            $("#copiar_url").tooltip('show');
        }
	</script>
@endpush
