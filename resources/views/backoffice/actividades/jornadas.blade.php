@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - ' . __('backend.jornadas'))

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'jornadas' ])

    <div class="tab-content">

        <div class="tab-pane active" id="jornadas">

           
            <div class="box box-primary">

                <div class="box-header with-border">
                    hola    </div>

                <div class="box-body">
                    <generic-datatable  
                        api-url="{{ '/admin/ajax/actividades/'. $actividad->idActividad .'/jornadas'}}"
                        fields="{{ $fieldsJornadas }}"
                        sort-order = "{{ $sortOrderJornadas }}"
                        placeholder-text="{{ __('backend.search_by_name_or_role') }}"
                        ref="jornadasTabla"
                    ></generic-datatable>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush