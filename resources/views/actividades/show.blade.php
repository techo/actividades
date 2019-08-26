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
                    <strong>Referente:</strong> {{isset($puntoEncuentro->responsable) ? $puntoEncuentro->responsable->nombreCompleto : "No definido"}}
                </div>
			</div>
		@endforeach
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
                    <a class="btn btn-link" data-toggle="modal" data-target="#compartirModal">
                        <i class="fas fa-share-alt"></i>COMPARTIR
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
	<script>
        function mostrarTooltip(){
            $("#copiar_url").tooltip({trigger: 'manual'});
            $("#copiar_url").tooltip('show');
        }
	</script>
@endpush
