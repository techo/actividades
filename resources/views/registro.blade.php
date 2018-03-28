@extends('main')

@section('page_title')
    Registro
@endsection

@section('main_image')
@endsection

@section('main_content')
	@if(isset($persona))
		<registro nombre="{{$persona->nombre}}" apellido="{{$persona->apellido}}" sexo="{{$persona->sexo}}" email="{{$persona->email}}" facebook_id="{{$persona->facebook_id}}" google_id="{{$persona->google_id}}" linkear={{$linkear}}></registro>
	@else
		<registro></registro>
	@endif
@endsection

@section('additional_scripts')
@endsection
