@extends('main')

@section('page_title')
    Detalle de Actividad
@endsection


@section('main_image')
    <div class="techo-hero">
        <img src="/img/hero-slim.jpg" alt="hero image" height="210">
        <h2>Inscríbete y acompáñanos con tu voluntariado</h2>
    </div>
@endsection

@section('main_content')

		<div class="row">
		@if (Auth::check() && Auth::user()->estaInscripto($actividad->idActividad))
		<div class="alert alert-success" id="alertYaInscripto">
    		<strong>Atentiii!</strong> Ya estas inscripto a esta actividad
  		</div>
		@endif
			<div class="col-md-12">
				<h4 class="card-subtitle">{{ $actividad->tipo->nombre }}</h3>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h1 class="card-title">{{ $actividad->nombreActividad }}</h1>
			</div>
		</div>
		<div class="row justify-content-start">
			<div class="col-md-2"><i class="far fa-calendar"></i> <span>{{ $actividad->fechaInicio->format('d-m-Y')}}</span></div>
			<div class="col-md-2"><i class="far fa-clock"></i> <span>{{ $actividad->fechaInicio->format('h:m')}}</span></div>
			<div class="col-md-2"><i class="fas fa-map-marker-alt"></i> <span>{{ $actividad->lugar }}</span></div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h2>Descripcion</h2>
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
				<h2>Coordinadores</h2>
			</div>
		</div>
		@foreach($actividad->puntosEncuentro as $puntoEncuentro)
			<div class="row">
				<div class="col-md-12">
				  	{{$puntoEncuentro->responsable->nombre_completo}}	
				</div>
			</div>
		@endforeach
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h2>Donde es la actividad</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<p>{{ $actividad->lugar }}</p>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h2>Puntos de encuentro</h2>
			</div>
		</div>
		@foreach($actividad->puntosEncuentro as $puntoEncuentro)
			<div class="row">
				<div class="col-md-12">
				  {{$puntoEncuentro->punto}}	
				</div>
			</div>
		@endforeach
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h2>Actividades relacionadas</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="card">
					<img src="https://placeholdit.co/i/555x150?bg=d3d3d3">
					<div class="row">
						<div class="col-md-12">
							<h6>[Tipo actividad]</h6>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h5>[Nombre actividad]</h5>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-4"><i class="far fa-calendar"></i> <span>[d-m-Y]</span></div>
						<div class="col-md-4"><i class="far fa-clock"></i> <span>[h:m]</span></div>
						<div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>[lugar]</span></div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-12">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
							tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
							quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
							consequat.
						</div>
					</div>

				</div>
			</div>
			<div class="col-md-4">
				<div class="card">
					<img src="https://placeholdit.co/i/555x150?bg=d3d3d3">
					<div class="row">
						<div class="col-md-12">
							<h6>[Tipo actividad]</h6>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h5>[Nombre actividad]</h5>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-4"><i class="far fa-calendar"></i> <span>[d-m-Y]</span></div>
						<div class="col-md-4"><i class="far fa-clock"></i> <span>[h:m]</span></div>
						<div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>[lugar]</span></div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-12">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
							tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
							quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
							consequat.
						</div>
					</div>

				</div>
			</div>
			<div class="col-md-4">
				<div class="card">
					<img src="https://placeholdit.co/i/555x150?bg=d3d3d3">
					<div class="row">
						<div class="col-md-12">
							<h6>[Tipo actividad]</h6>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h5>[Nombre actividad]</h5>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-4"><i class="far fa-calendar"></i> <span>[d-m-Y]</span></div>
						<div class="col-md-4"><i class="far fa-clock"></i> <span>[h:m]</span></div>
						<div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>[lugar]</span></div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-12">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
							tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
							quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
							consequat.
						</div>
					</div>

				</div>
			</div>

		</div>
@endsection

@section('footer')
<footer class="row fixed-bottom align-middle inscripcion-bar">
	<div class="col-md-8"><h5>{{ $actividad->nombreActividad }}</h5></div>
	<div class="col-md-2 text-primary"><i class="fas fa-share-alt"></i> COMPARTIR</div>
	@if (Auth::check() && Auth::user()->estaInscripto($actividad->idActividad))
		<div class="col-md-2"><span class="btn btn-success">YA TE INSCRIBISTE!</span></div>
	@else
		<div class="col-md-2"><a class="btn btn-primary inscripcion-btn" href="/inscripciones/actividad/{{$actividad->idActividad}}">INSCRIBIRME</a></div>
	@endif
</footer>
@endsection