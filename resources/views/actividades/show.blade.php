@extends('main')

@section('page_title')
    {{ optional($actividad->tipo)->nombre_localizado }}:  {{ $actividad->nombreActividad }}
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
				<h6 class="card-subtitle text-uppercase font-weight-bold" style="color:{{$actividad->tipo->categoria->color}}">{{ $actividad->tipo->nombre_localizado }}</h6>
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
                    <div class="descripcion-box" id="descripcionBox">
                        <div class="descripcion-texto" id="descripcionTexto">
                            {!! $actividad->descripcion !!}
                        </div>
                        <div class="descripcion-fade" id="descripcionFade"></div>
                    </div>
                    <div class="text-center mt-2" id="descripcionVerMasWrap" style="display:none;">
                        <button type="button" class="btn btn-link" id="descripcionVerMas">{{ __('frontend.read_more') }}</button>
                    </div>
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
                    @foreach($actividad->coordinadores as $coordinador)
                    @if($coordinador->persona)
                    <span style="display: none;">
                        {{ $coordinador->persona->nombres }} {{ $coordinador->persona->apellidoPaterno }}
                    </span>
                    @endif
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
            <div class="row">
                <div class="col-md-12">
                    <h5>{{ __('frontend.meeting_points') }}</h5>
                </div>
            </div>

            <div class="row">
                @foreach($actividad->puntosEncuentro as $puntoEncuentro)
                    @if($puntoEncuentro->estado)
                        <div class="col-md-6 mb-3">
                            <div class="border p-3 h-100" style="border-radius:10px;">
                                <strong><i class="fas fa-map-marker-alt mr-1 text-primary"></i>{{ $puntoEncuentro->punto }}</strong>
                                <div class="text-muted" style="font-size:.9rem;">
                                    @php
                                        echo isset($puntoEncuentro->localidad->localidad) ? e($puntoEncuentro->localidad->localidad) . ', ' : '';
                                        echo isset($puntoEncuentro->provincia->provincia) ? e($puntoEncuentro->provincia->provincia) : '';
                                    @endphp
                                </div>
                                <div class="mt-2" style="font-size:.9rem;">
                                    <strong>{{ __('frontend.referring') }}:</strong>
                                    @if ($puntoEncuentro->responsable)
                                        @if ($puntoEncuentro->responsable->photo)
                                            <img class="imagen-perfil-mini" src="{{ '/'.$puntoEncuentro->responsable->photo }}" alt="Foto">
                                        @else
                                            <img src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-mini" alt="User Image">
                                        @endif
                                        {{ $puntoEncuentro->responsable->nombreCompleto }}
                                    @else
                                        {{ __('frontend.not_defined') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
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
        var box  = document.getElementById('descripcionBox');
        var txt  = document.getElementById('descripcionTexto');
        var wrap = document.getElementById('descripcionVerMasWrap');
        var btn  = document.getElementById('descripcionVerMas');
        var fade = document.getElementById('descripcionFade');
        if (!box || !txt) return;

        // Si el texto no supera el alto colapsado, se muestra completo (sin fade ni botón).
        function evaluar() {
            if (txt.scrollHeight <= txt.clientHeight + 5) {
                box.classList.add('expandida');
                if (fade) fade.style.display = 'none';
                if (wrap) wrap.style.display = 'none';
            } else if (wrap) {
                wrap.style.display = 'block';
            }
        }
        // Tras el render de Vue y con las imágenes de la descripción ya cargadas.
        setTimeout(evaluar, 350);
        $(window).on('load', evaluar);

        if (btn) btn.addEventListener('click', function () {
            var abierta = box.classList.toggle('expandida');
            btn.textContent = abierta ? @json(__('frontend.read_less')) : @json(__('frontend.read_more'));
        });
    });
</script>
@endpush

@push('additional_styles')
    <style>
        .descripcion-box { position: relative; }
        .descripcion-texto { max-height: 180px; overflow: hidden; transition: max-height .4s ease; }
        .descripcion-box.expandida .descripcion-texto { max-height: 4000px; }
        .descripcion-fade { position: absolute; left: 0; right: 0; bottom: 0; height: 70px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
            pointer-events: none; transition: opacity .3s; }
        .descripcion-box.expandida .descripcion-fade { opacity: 0; }
    </style>
@endpush
