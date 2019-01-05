@extends('main')

@section('page_title')
    {{ $actividad->tipo->nombre }}:  {{ $actividad->nombreActividad }}
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        {{--<img src="/img/hero-slim.jpg" alt="hero image">--}}
        <h2></h2>
    </div>
@endsection

@section('main_content')
		<div class="row">
        @if (Auth::check() && Auth::user()->estaPreInscripto($actividad->idActividad))
            <div class="alert alert-warning" id="alertYaInscripto">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Estás pre-inscripto a esta actividad</strong>
            </div>
        @elseif (Auth::check() && Auth::user()->estaInscripto($actividad->idActividad))
            <div class="alert alert-success" id="alertYaInscripto">
                <i class="fas fa-check-circle"></i>
                <strong>Ya estas inscripto a esta actividad</strong>
        </div>
		@elseif(!$hayCupos)
			<div class="alert alert-danger" id="alertYaInscripto">
                <i class="fas fa-times-circle"></i>
				<strong>La actividad no tiene más cupos</strong>
			</div>
		@elseif(!$inscripcionAbierta)
			<div class="alert alert-danger" id="alertYaInscripto">
                <i class="fas fa-times-circle"></i>
				<strong>El período de inscripción está cerrado</strong>
			</div>
		@endif
		</div>
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
			<div class="col-md-2"><i class="far fa-calendar"></i> <span>{{ $actividad->fechaInicio->format('d/m/Y')}}</span></div>
			<div class="col-md-2"><i class="far fa-clock"></i> <span>{{ $actividad->fechaInicio->format('H:i')}}hs</span></div>

            <div class="col-md-8">
                <i class="fas fa-map-marker-alt"></i>
                <span>
					@if (!isset($actividad->localidad) || $actividad->localidad->localidad == "No definida")
                        {{ $actividad->provincia->provincia }}, {{ $actividad->pais->nombre }}
                    @elseif (!isset($actividad->provincia))
                        Sin especificar
                    @else
                        {{ $actividad->localidad->localidad }}, {{ $actividad->provincia->provincia }}, {{ $actividad->pais->nombre }}
                    @endif
                </span>
            </div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h5>Descripción</h5>
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
                <h5>Coordinador de la Actividad</h5>
			</div>
		</div>
			<div class="row">
				<div class="col-md-12">
                    {{ isset($actividad->coordinador) ? $actividad->coordinador->nombreCompleto : "No definido" }}
				</div>
			</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h5>Puntos de encuentro</h5>
			</div>
		</div>
		@foreach($actividad->puntosEncuentro as $puntoEncuentro)
			<div class="row">
                <div class="col-md-4">
				{{$puntoEncuentro->punto}}
                </div>
                <div class="col-md-4">
                    @php
                        echo isset($puntoEncuentro->localidad->localidad) ? $puntoEncuentro->localidad->localidad . ', ': '';
                        echo isset($puntoEncuentro->provincia->provincia) ? $puntoEncuentro->provincia->provincia . ', ': '';
                        echo isset($puntoEncuentro->pais->nombre) ? $puntoEncuentro->pais->nombre : '';
                    @endphp

                </div>
                <div class="col-md-4">
                    <strong>Responsable:</strong> {{isset($puntoEncuentro->responsable) ? $puntoEncuentro->responsable->nombreCompleto : "No definido"}}
                </div>
			</div>
		@endforeach
@endsection

@section('footer')
<footer class="footer inscripcion-bar fixed-bottom">
    <div class="container">
        <div class="row">
            <div class="col-md-7">
                <p class="h5">{{ $actividad->nombreActividad }}</p>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-link" data-toggle="modal" data-target="#compartirModal">
                    <i class="fas fa-share-alt"></i>  COMPARTIR
                </button>
            </div>
            <div class="col-md-3">
                @if (Auth::check() && Auth::user()->estaPreInscripto($actividad->idActividad))
                    <div>
                        <a
                            href="{{ action('InscripcionesController@confirmarDonacion', ['id' => $actividad->idActividad]) }}"
                            class="btn btn-primary"
                        >
                            Confirmar participación
                        </a>
                    </div>
                @elseif (Auth::check() && Auth::user()->estaInscripto($actividad->idActividad))
                    <div><span class="btn btn-success w-100"><strong>¡YA TE INSCRIBISTE!</strong></span></div>
                @elseif($hayCupos && $inscripcionAbierta)
                    <div>
                        <a class="btn btn-primary inscripcion-btn w-100"
                           href="/inscripciones/actividad/{{$actividad->idActividad}}">
                            <strong>INSCRIBIRME<strong>
                        </a>
                    </div>
                @elseif(!$hayCupos)
                    <div class="alert alert-danger" id="alertYaInscripto">
                        <i class="fas fa-times-circle"></i>
                        <strong>La actividad no tiene más cupos</strong>
                    </div>
                @elseif(!$inscripcionAbierta)
                    <div class="alert alert-danger" id="alertYaInscripto">
                        <i class="fas fa-times-circle"></i>
                        <strong>El período de inscripción está cerrado</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</footer>


@endsection

@section('aditional_html')
	@include('partials.compartir-modal', ['url' => Request::url(), 'title' => $actividad->nombreActividad])
@endsection

@push('additional_scripts')
	<script>
        function mostrarTooltip(){
            $("#copiar_url").tooltip({trigger: 'manual'});
            $("#copiar_url").tooltip('show');
        }
	</script>
@endpush
