@extends('backoffice.main')

@section('page_title', 'Generales')

@section('content')

@include('backoffice.estadisticas.filtros')

<div class="box">
	<div class="box-body">

		<h3>Inscripciones</h3>
		<table class="table table-bordered">
			<thead>
			    <tr>
			      <th scope="col">Mes</th>
			      <th scope="col">Inscripciones</th>
			      <th scope="col">Presentes</th>
			    </tr>
		  	</thead>
		  	<tbody>
				@foreach ($inscripciones as $mes)
					<tr>
						<td>{{ $mes->mes }}</td> 
						<td>{{ $mes->inscripciones }}</td> 
						<td>{{ $mes->presentes }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<br>

		<h3>Actividades</h3>
		<table class="table table-bordered">
			<thead>
			    <tr>
			      <th scope="col">Mes</th>
			      <th scope="col">Cantidad</th>
			      <th scope="col">En territorio</th>
			      <th scope="col">En oficinas y de formación</th>
			      <th scope="col">En eventos</th>
			    </tr>
		  	</thead>
		  	<tbody>
				@foreach ($actividades as $mes)
					<tr>
						<td>{{ $mes->mes }}</td> 
						<td>{{ $mes->total }}</td> 
						<td>{{ $mes->territorio }}</td>
						<td>{{ $mes->oficinas }}</td>
						<td>{{ $mes->eventos }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

	</div>
</div>
@endsection