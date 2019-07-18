@extends('backoffice.main')

@section('page_title', 'Personas')

@section('content')

@include('backoffice.estadisticas.filtros')
<div class="box">
	<div class="box-body">

		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#coordinadores" data-toggle="tab" >Coordinadores</a></li>
				<li class=""><a href="#inscripciones" data-toggle="tab" >Inscriptos</a></li>
				<li class=""><a href="#evaluaciones" data-toggle="tab" >Evaluados</a></li>
			</ul>
		</div>

		<div class="tab-content">
			<div id="coordinadores" class="tab-pane active">

				<p>Voluntarios movilizados por los coordinadores</p>
				<table class="table table-bordered">
					<thead>
					    <tr>
					      <th scope="col">Nombre</th>
					      <th scope="col">Apellido</th>
					      <th scope="col">Inscripciones</th>
					      <th scope="col">Presentes</th>
					    </tr>
				  	</thead>
				  	<tbody>
						@foreach ($top_coordinadores_mas_convocantes as $persona)
							<tr>
								<td>{{ $persona->nombres }}</td> 
								<td>{{ $persona->apellidoPaterno }}</td> 
								<td>{{ $persona->inscripciones }}</td> 
								<td>{{ $persona->presentes }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>

			</div>

			<div id="inscripciones" class="tab-pane">

				<p>A cuantas actividades se inscribieron las personas</p>
				<table class="table table-bordered">
					<thead>
					    <tr>
					      <th scope="col">Nombre</th>
					      <th scope="col">Apellido</th>
					      <th scope="col">Inscripciones</th>
					      <th scope="col">Presentes</th>
					    </tr>
				  	</thead>
				  	<tbody>
						@foreach ($top_personas_mas_motivadas as $persona)
							<tr>
								<td>{{ $persona->nombres }}</td> 
								<td>{{ $persona->apellidoPaterno }}</td> 
								<td>{{ $persona->inscripciones }}</td> 
								<td>{{ $persona->presentes }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>

			</div>

			<div id="evaluaciones" class="tab-pane active">

				<h3>Evaluaciones sociales</h3>
				<table class="table table-bordered">
					<thead>
					    <tr>
					      <th scope="col">Nombre</th>
					      <th scope="col">Apellido</th>
					      <th scope="col">Puntaje</th>
					      <th scope="col">Cantidad</th>
					    </tr>
				  	</thead>
				  	<tbody>
						@foreach ($top_personas_con_peores_evaluaciones_sociales as $persona)
							<tr>
								<td>{{ $persona->nombres }}</td> 
								<td>{{ $persona->apellidoPaterno }}</td> 
								<td>{{ $persona->puntaje }}</td> 
								<td>{{ $persona->cantidad }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>

				<h3>Evaluaciones técnicas</h3>
				<table class="table table-bordered">
					<thead>
					    <tr>
					      <th scope="col">Nombre</th>
					      <th scope="col">Apellido</th>
					      <th scope="col">Puntaje</th>
					      <th scope="col">Cantidad</th>
					    </tr>
				  	</thead>
				  	<tbody>
						@foreach ($top_personas_con_peores_evaluaciones_tecnicas as $persona)
							<tr>
								<td>{{ $persona->nombres }}</td> 
								<td>{{ $persona->apellidoPaterno }}</td> 
								<td>{{ $persona->puntaje }}</td> 
								<td>{{ $persona->cantidad }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection