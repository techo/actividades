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
        @if ($inscripcionConfirmada)
            <div class="row align-items-center">
                <div class="col">
                    <button class="btn btn-white mb-2 d-flex align-items-center justify-content-between w-100 p-0" type="button" data-toggle="collapse" data-target="#collapseActividad" aria-expanded="false" aria-controls="collapseActividad">
                        <span class="h5 m-0">{{ __('frontend.description') }}</span> 
                        <i class="fas fa-chevron-down" id="iconoDescripcion"></i>
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseActividad">
                <div class="row">
                    <div class="col-md-12 px-4">
                        {!! $actividad->descripcion !!}
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-md-12">
                    {!! $actividad->descripcion !!}
                </div>
            </div>
        @endif

		<hr>
		<div class="row">
			<div class="col-md-12">
                <h5>{{ __('frontend.coordinator') }}</h5>
			</div>
		</div>
        <div class="row">
            <div class="col-md-12 p-1 ">
                    @foreach($actividad->coordinadores as $coordinador)
                    <span style="display: none;">
                        {{ $coordinador->persona->nombres }} {{ $coordinador->persona->apellidoPaterno }}
                    </span>                   
                    @endforeach
                    <persona-tooltip
                        :personas='@json($actividad->coordinadores)'
                    />
                
            </div>
        </div>
		<hr>
        @if(filled($qrCode) && $qrCode != false)
            <div class="row justify-content-center text-center">
                <div class="col-md-12">
                    <h5>{{ __('frontend.confirm_inscription_with_qr') }}</h5>
                    <span>{{ __('frontend.show_on_arrival') }}</span>
                </div>
            </div>
            <div class="d-flex justify-content-center m-2">
                {!! $qrCode !!}
            </div>
            <hr class="mx-auto" style="width: 80%;">
        @endif
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

        @if ($inscripcionConfirmada && $inscriptos != '')
            <hr>
            <div class="row justify-content-center text-left">
                <div class="col-md-12">
                    <h5>{{ __('frontend.meet_your_new_community') }}</h5>
                    <inscripto-tooltip
                        :inscriptos='@json($inscriptos)'    
                    />
                </div>
            </div>
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
                    @if ($inscripcionConfirmada && $actividad->chat_grupal_whatsapp != null)
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

<script>
    $(document).ready(function () {
        $('#collapseActividad').on('show.bs.collapse', function () {
            $('#iconoDescripcion').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        });

        $('#collapseActividad').on('hide.bs.collapse', function () {
            $('#iconoDescripcion').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });
    });
</script>
@endpush
