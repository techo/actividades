@extends('main')

@section('page_title')
    {{ __('frontend.inscription_confirmed') }}
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="card-subtitle">{{ __('frontend.inscription_confirmed') }}</h1>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-8">
            <h3 class="card-title">
                <br>
                {{ __('frontend.with_this_donation') }}
                <a href="/actividades/{{$actividad->idActividad}}">
                    {{ $actividad->nombreActividad }}
                </a>
            </h3>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-8">
            <p>
                {{ __('frontend.important_remainder') }}
            </p>
            <p>
                <strong>{{ __('frontend.meeting_points') }}</strong><br>
                {{ $inscripcion->punto_encuentro->punto }}, {{ \Carbon\Carbon::parse($inscripcion->punto_encuentro->horario)->format('H:i') }} hs
            </p>
            <p>{{ __('frontend.coordinator') }} {{ $inscripcion->punto_encuentro->responsable->nombreCompleto }}
                (<a href="mailto:{{$inscripcion->punto_encuentro->responsable->mail}}">
                    {{$inscripcion->punto_encuentro->responsable->mail}}</a>), {{ __('frontend.any_doubt_contact') }}
            </p>
            <p>{{ __('frontend.activity_takes_place') }} 
                {{ $actividad->localidad->localidad }},
                {{ $actividad->provincia->provincia }},
                {{ $actividad->pais->nombre }}
            </p>
            <p>{{ __('frontend.activity_starts_at') }} {{ $actividad->fechaInicio->format('d/m/Y H:i') }}</p>
            <p>{{ __('frontend.activity_ends_at') }} {{ $actividad->fechaFin->format('d/m/Y H:i') }}</p>
        
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