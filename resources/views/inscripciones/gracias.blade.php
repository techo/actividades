@extends('main')

@section('page_title')
    {{ __('frontend.activity_detail') }}
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h2 class="card-subtitle"> {{ __('frontend.inscription_confirmed') }}</h2>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-8">
            <h3 class="card-title">
                <br>
                {{ __('frontend.already_inscripted') }}
                <a href="/actividades/{{$actividad->idActividad}}" class="link">
                    {{ $actividad->nombreActividad }}
                </a>
            </h3>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-8">
            <p>
                {{ __('frontend.mail_message') }} <a href="/perfil/actividades">{{ __('frontend.my_activities') }}</a>.
            </p>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-2">
            <button type="button" class="btn btn-link" data-toggle="modal" data-target="#compartirModal">
                <i class="fas fa-share-alt"></i>  {{ __('frontend.share') }}
            </button>
        </div>
        <div class="col-md-2">
            <a href="/" class="btn btn-link">{{ __('frontend.go_back') }}</a>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('aditional_html')
    @include(
        'partials.compartir-modal',
        ['url' => action('ActividadesController@show', ['id' => $actividad->idActividad]),
        'title' => $actividad->nombreActividad]
    )
@endsection