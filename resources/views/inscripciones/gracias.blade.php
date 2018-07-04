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
			<div class="col-md-12">
				<h1 class="card-subtitle">¡Inscripción confirmada! Gracias por ayudar.</h3>
			</div>
		<hr>
		</div>
		<div class="row">
			<div class="col-md-8">
				<h3 class="card-title">Estas inscripto a <a href="/actividades/{{$actividad->idActividad}}">{{ $actividad->nombreActividad }}</a></h1>
			</div>
		</div>
		<div class="row justify-content-start">
			<div class="col-md-8">
				<p>Te hemos enviado por mail toda la información pertinente para la actividad</p>
				<p>Puedes ingresar al panel de usuario para visualizar o modificar tu presencia</p>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h2>Descripción</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<p>{!! $actividad->descripcion !!}</p>
			</div>
		</div>
		<div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h2>Coordinadores</h2>
			</div>
		</div>
		<div class="row">
			@foreach($actividad->puntosEncuentro as $puntoEncuentro)
				<div class="col-md-2">
				<div class="text-center">
					<div class="mx-auto rounded-circle coordinador-img">
						<h4>{{ strtoupper(substr($puntoEncuentro->responsable->nombres,0,1)) . strtoupper(substr($puntoEncuentro->responsable->apellidoPaterno,0,1)) }}</h4>
					</div>
					<span>{{$puntoEncuentro->responsable->nombre_completo}}</span>
				</div>
				</div>
			@endforeach
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<h2>Donde es la actividad</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<p>{{ $actividad->localidad->localidad . ", " . $actividad->provincia->provincia . ", " . $actividad->pais->nombre }}</p>
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
				  {{$puntoEncuentro->punto}} en {{ $puntoEncuentro->localidad->localidad . ", " . $puntoEncuentro->provincia->provincia . ", " . $puntoEncuentro->pais->nombre}}
				</div>
			</div>
		@endforeach
		{{-- COMING SOON --}}
		{{--<hr>--}}
		{{--<div class="row">--}}
			{{--<div class="col-md-12">--}}
				{{--<h2>Actividades relacionadas</h2>--}}
			{{--</div>--}}
		{{--</div>--}}
		{{--<div class="row">--}}
			{{--<div class="col-md-4">--}}
				{{--<div class="card">--}}
					{{--<img src="https://placeholdit.co/i/555x150?bg=d3d3d3">--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--<h6>[Tipo actividad]</h6>--}}
						{{--</div>--}}
					{{--</div>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--<h5>[Nombre actividad]</h5>--}}
						{{--</div>--}}
					{{--</div>--}}
					{{--<hr>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-4"><i class="far fa-calendar"></i> <span>[d-m-Y]</span></div>--}}
						{{--<div class="col-md-4"><i class="far fa-clock"></i> <span>[h:m]</span></div>--}}
						{{--<div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>[lugar]</span></div>--}}
					{{--</div>--}}
					{{--<hr>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod--}}
							{{--tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,--}}
							{{--quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo--}}
							{{--consequat.--}}
						{{--</div>--}}
					{{--</div>--}}

				{{--</div>--}}
			{{--</div>--}}
			{{--<div class="col-md-4">--}}
				{{--<div class="card">--}}
					{{--<img src="https://placeholdit.co/i/555x150?bg=d3d3d3">--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--<h6>[Tipo actividad]</h6>--}}
						{{--</div>--}}
					{{--</div>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--<h5>[Nombre actividad]</h5>--}}
						{{--</div>--}}
					{{--</div>--}}
					{{--<hr>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-4"><i class="far fa-calendar"></i> <span>[d-m-Y]</span></div>--}}
						{{--<div class="col-md-4"><i class="far fa-clock"></i> <span>[h:m]</span></div>--}}
						{{--<div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>[lugar]</span></div>--}}
					{{--</div>--}}
					{{--<hr>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod--}}
							{{--tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,--}}
							{{--quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo--}}
							{{--consequat.--}}
						{{--</div>--}}
					{{--</div>--}}

				{{--</div>--}}
			{{--</div>--}}
			{{--<div class="col-md-4">--}}
				{{--<div class="card">--}}
					{{--<img src="https://placeholdit.co/i/555x150?bg=d3d3d3">--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--<h6>[Tipo actividad]</h6>--}}
						{{--</div>--}}
					{{--</div>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--<h5>[Nombre actividad]</h5>--}}
						{{--</div>--}}
					{{--</div>--}}
					{{--<hr>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-4"><i class="far fa-calendar"></i> <span>[d-m-Y]</span></div>--}}
						{{--<div class="col-md-4"><i class="far fa-clock"></i> <span>[h:m]</span></div>--}}
						{{--<div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>[lugar]</span></div>--}}
					{{--</div>--}}
					{{--<hr>--}}
					{{--<div class="row">--}}
						{{--<div class="col-md-12">--}}
							{{--Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod--}}
							{{--tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,--}}
							{{--quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo--}}
							{{--consequat.--}}
						{{--</div>--}}
					{{--</div>--}}

				{{--</div>--}}
			{{--</div>--}}

		{{--</div>--}}
@endsection