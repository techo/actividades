@extends('main')

@section('page_title')
    {{ __('suscribe.title') }}
@endsection

@section('main_image')

@endsection

@section('main_content')

    <suscribe :pais="{{ json_encode($pais) }}"></suscribe>

@endsection
