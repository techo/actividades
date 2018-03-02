<!DOCTYPE html>
<html>
<head>
    <title>Detalle </title>
	<script src="/js/fontawesome-all.min.js"></script>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
<div class="container"  id="app">
	<div class="card">
		<div class="row">
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
		<hr>
		<div class="row  align-middle">
			<div class="col-md-8"><h5>{{ $actividad->nombreActividad }}</h5></div>
			<div class="col-md-2 text-primary"><i class="fas fa-share-alt"></i> COMPARTIR</div>
			<div class="col-md-2"><a class="btn btn-primary" href="/inscripciones/actividad/{{$actividad->idActividad}}">INSCRIBIRME</a></div>
		</div>


	</div>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->

{{--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>--}}
<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/vue"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.9.4/holder.js"></script>
<script>
    Holder.addTheme('thumb', {
        bg: '#55595c',
        fg: '#eceeef',
        text: 'Thumbnail'
    });
</script>

</body>
</html>
