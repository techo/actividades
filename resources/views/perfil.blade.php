@extends('main')

@section('page_title')
    Registro
@endsection

@section('main_image')
@endsection

@section('main_content')
	<perfil usuario="{{Auth::user()}}"></perfil>
@endsection

@section('additional_scripts')
@endsection
