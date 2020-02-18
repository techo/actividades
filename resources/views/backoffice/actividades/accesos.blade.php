@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - evaluaciones')

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'accesos' ])

    <div class="tab-content">

        <div class="tab-pane active" id="accesos">
            <accesos :id="{{ $actividad->idActividad }}"></accesos>
        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush