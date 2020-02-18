@extends('main')

@section('page_title')
    {{ __('frontend.meeting_points') }}
@endsection


@section('main_image')
@endsection

@section('main_content')
    <inscripcion id="{{$actividad->idActividad}}" csrf_token="{{ csrf_token() }}"></inscripcion>
@endsection

@section('footer')
    @include('partials.footer')
@endsection