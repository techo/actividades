@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - puntos')

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'puntos' ])

    <div class="tab-content">

        <div class="tab-pane active" id="puntos">
            <puntos id="{{ $actividad->idActividad }}" fields="{{ $fieldsPuntos }}" sort-order="{{ $sortOrderPuntos }}" ></puntos>
        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush