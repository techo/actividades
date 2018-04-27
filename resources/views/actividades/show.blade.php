@extends('main')

@section('page_title')
    {{ $actividad->tipo->nombre }}:  {{ $actividad->nombreActividad }}
@endsection


@section('main_image')
    <div class="techo-hero">
        <img src="/img/hero-slim.jpg" alt="hero image" height="210">
    </div>
@endsection

@section('main_content')
		<div class="row">
		@if (Auth::check() && Auth::user()->estaInscripto($actividad->idActividad))
			<div class="alert alert-success" id="alertYaInscripto">
				<strong>Ya estas inscripto a esta actividad</strong>
			</div>
		@elseif(!$hayCupos)
			<div class="alert alert-danger" id="alertYaInscripto">
				<strong>La actividad no tiene más cupos</strong>
			</div>
		@elseif(!$inscripcionAbierta)
			<div class="alert alert-danger" id="alertYaInscripto">
				<strong>El período de inscripción está cerrado</strong>
			</div>
		@endif
			<div class="col-md-12">
				<h6 class="card-subtitle text-uppercase font-weight-bold">{{ $actividad->tipo->nombre }}</h6>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2 class="card-title">{{ $actividad->nombreActividad }}</h2>
			</div>
		</div>
		<div class="row justify-content-start">
			<div class="col-md-2"><i class="far fa-calendar"></i> <span>{{ $actividad->fechaInicio->format('d-m-Y')}}</span></div>
			<div class="col-md-2"><i class="far fa-clock"></i> <span>{{ $actividad->fechaInicio->format('h:m')}}</span></div>
            <div class="col-md-8">
                <i class="fas fa-map-marker-alt"></i>
                <span>
                    {{ $actividad->localidad->localidad }}, {{ $actividad->provincia->provincia }}
                    , {{ $actividad->pais->nombre }}
                </span>
            </div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h5>Descripcion</h5>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<p>{{ $actividad->descripcion }}</p>
			</div>
		</div>
		<div>
		<hr>
		<div class="row">
			<div class="col-md-12">
                <h5>Coordinador de la Actividad</h5>
			</div>
		</div>
			<div class="row">
				<div class="col-md-12">
                    {{ $actividad->coordinador->nombreCompleto }}
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
                    <strong>Coordinador:</strong> {{$puntoEncuentro->responsable->nombreCompleto}}
                </div>
			</div>
		@endforeach
            <br>
            <p class="text-muted text-right">
                * Tendrás más detalle sobre los puntos de encuentro cuando completes tu inscripción.
            </p>

@endsection

@section('footer')
<footer class="row fixed-bottom align-middle inscripcion-bar">
	<div class="col-md-7"><h5>{{ $actividad->nombreActividad }}</h5></div>
	<div class="col-md-2">
		<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#compartirModal">
			<i class="fas fa-share-alt"></i>  COMPARTIR
		</button>
	</div>
	@if (Auth::check() && Auth::user()->estaInscripto($actividad->idActividad))
		<div class="col-md-3"><span class="btn btn-success w-100">YA TE INSCRIBISTE!</span></div>
	@elseif($hayCupos && $inscripcionAbierta)
        <div class="col-md-3">
            <a class="btn btn-primary inscripcion-btn w-100"
               href="/inscripciones/actividad/{{$actividad->idActividad}}">
                INSCRIBIRME
            </a>
        </div>
	@else
        <div class="col-md-3">
            <span class="btn btn-danger inscripcion-btn w-100">
                INSCRIPCIÓN CERRADA
            </span>
        </div>
	@endif
</footer>

@endsection

@section('aditional_html')
	@include('partials.compartir-modal', ['url' => Request::url(), 'title' => $actividad->nombreActividad])
@endsection