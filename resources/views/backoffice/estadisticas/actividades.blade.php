@extends('backoffice.main')

@section('page_title', 'Actividades')

@section('content')

@include('backoffice.estadisticas.filtros')

<div class="box">
	<div class="box-body">

		<h3>Top actividades más convocantes</h3>
		<table class="table table-bordered">
			<thead>
			    <tr>
			      <th scope="col">Actividad</th>
			      <th scope="col">Inscripciones</th>
			      <th scope="col">Presentes</th>
			    </tr>
		  	</thead>
		  	<tbody>
				@foreach ($top_actividades_mas_convocantes as $actividad)
					<tr>
						<td>{{ $actividad->nombreActividad }}</td> 
						<td>{{ $actividad->inscripciones }}</td> 
						<td>{{ $actividad->presentes }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<br>

		<h3>Top actividades con mejores evaluaciones</h3>
		<table class="table table-bordered">
			<thead>
			    <tr>
			      <th scope="col">Actividad</th>
			      <th scope="col">Inscripciones</th>
			      <th scope="col">Presentes</th>
			    </tr>
		  	</thead>
		  	<tbody>
				@foreach ($top_actividades_con_mejores_evaluaciones as $actividad)
					<tr>
						<td>{{ $actividad->nombreActividad }}</td> 
						<td>{{ $actividad->puntaje }}</td> 
						<td>{{ $actividad->cantidad }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

@endsection