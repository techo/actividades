@extends('main')

@section('page_title')
    AutoTest Voluntariado
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <h2 class="text-uppercase mb-5">
            ¿Cuál es la actividad perfecta para vos?
        </h2>
    </div>
@endsection

@section('main_content')

    <quiz-techero ref="autotest"></quiz-techero>

@endsection
