@extends('main')

@section('page_title')
    {{ __('frontend.profile') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
	<perfil usuario="{{json_encode($usuario)}}"></perfil>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
