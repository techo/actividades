@extends('backoffice.main')

@section('page_title', 'Personas')

@section('content')

@include('backoffice.estadisticas.filtros')
<div class="box">
	<div class="box-body">
		<h3>Top coordinadores más convocantes</h3>
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

		<br>

		<h3>Top personas más motivadas</h3>
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

		<br>

		<h3>Top personas con peores evaluaciones sociales</h3>
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

		<br>

		<h3>Top personas con peores evaluaciones técnicas</h3>
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

@endsection