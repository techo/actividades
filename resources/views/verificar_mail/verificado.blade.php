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
			<div class="col-md-8">
				<h3 class="card-subtitle">Verificacion e-mail de usuario</h3>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-8">
				<h4 class="card-subtitle">
					{{$status}}
				</h4>
			</div>
		</div>
@endsection