@extends('backoffice.main')

@section('page_title', 'Actividades')

@section('content')

<div class="box">
	<div class="box-body">

		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#inscripciones" data-toggle="tab" >Inscripciones</a></li>
				<li class=""><a href="#evaluaciones" data-toggle="tab" >Evaluaciones</a></li>
			</ul>
		</div>

		<div class="tab-content">
			<div id="inscripciones" class="tab-pane active">

				<p>Insriptos y presentes por actividad</p>
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
			</div>

			<div id="evaluaciones" class="tab-pane">

				<p>Promedio de evaluaciones (de actividad)</p>
				<table class="table table-bordered">
					<thead>
					    <tr>
					      <th scope="col">Actividad</th>
					      <th scope="col">Puntaje</th>
					      <th scope="col">Cantidad</th>
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

	</div>
</div>

@endsection