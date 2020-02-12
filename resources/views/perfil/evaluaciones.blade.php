@extends('main')

@section('page_title')
    {{ __('frontend.my_score') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
    <h1>{{ __('frontend.my_score') }}</h1>

    <evaluacion-personal :puntaje-tecnico="{{ $evaluacionPersonal[0]->puntajeTecnico}}" :puntaje-social="{{ $evaluacionPersonal[0]->puntajeSocial}}" ></evaluacion-personal>
    <br>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
