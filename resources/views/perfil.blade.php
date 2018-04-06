@extends('main')

@section('page_title')
    Perfil
@endsection

@section('main_image')
@endsection

@section('main_content')
	<perfil usuario="{{json_encode($usuario)}}"></perfil>
@endsection

@section('additional_scripts')
@endsection
