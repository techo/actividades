@extends('main')

@section('page_title')
    {{ __('frontend.my_score') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
    <h1>{{ __('frontend.my_score') }}</h1>


    @if ( $promedioTecnico > 4) 
    	<evaluacion-personal :puntaje-tecnico="{{ round($promedioTecnico,1)}}" :puntaje-social="{{ round($promedioSocial,1) }}" :puntaje-genero="{{ round($promedioGenero,1)}}" ></evaluacion-personal>
    @else
    	<p> {{ __('frontend.my_score_not_active') }}<p>
    @endif
    <br>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
