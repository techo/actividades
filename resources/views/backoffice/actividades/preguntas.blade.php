@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - ' . __('backend.preguntas_inscripcion'))

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'preguntas' ])

    <div class="tab-content">

        <div class="tab-pane active" id="preguntas">
            <div class="row vertical-align">
                <div class="col-md-12">
                    <h3 class="pull-left">{{ __('backend.preguntas_inscripcion') }}</h3>
                </div>
            </div>
            <preguntas-actividad :actividad-id="{{ $actividad->idActividad }}"></preguntas-actividad>
        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush
