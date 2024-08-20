@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - ' . __('backend.jornadas'))

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'jornadas' ])

    <div class="tab-content">

        <div class="tab-pane active" id="jornadas">
            <jornadas 
                actividad="{{ json_encode($actividad) }}"
                id-actividad="{{ $actividad->idActividad }}" 
                actividad-inicio="{{ $actividad->fechaInicio }}" 
                actividad-fin="{{ $actividad->fechasFin }}" 
                fields="{{ $fieldsJornadas }}" 
                sort-order="{{ $sortOrderJornadas }}"
                api-url="{{ '/admin/ajax/actividades/'. $actividad->idActividad .'/jornadas'}}" >
            </jornadas>
        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush