@extends('backoffice.main')

@section('page_title', 'Nueva Actividad')

@section('subtitulo')

@endsection

@section('content')
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#general" data-toggle="tab" aria-expanded="true">General</a>
            </li>
            <li class="disabled">
                <a href="#" data-toggle="" aria-expanded="true">Grupos</a>
            </li>
            <li class="disabled">
                <a href="#" data-toggle="" aria-expanded="true">Inscripciones</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="general">
                <actividades-show
                        actividad="{{ $actividad }}"
                        paises="{{ $paises }}"
                        localidades=""
                        provincias=""
                        tipos=""
                        categorias="{{ $categorias }}"
                        edicion="{{ $edicion }}"
                ></actividades-show>
            </div>
            <div class="tab-pane" id="grupos">
            </div>
            <div class="tab-pane" id="inscripciones">
            </div>
        </div>
    </div>
    <p style="margin-bottom: 4em"></p>
@endsection

@push('additional_scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endpush

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@section('footer')
    <crud-footer
            cancelar-url="/admin/actividades"
            edicion="{{ $edicion }}"
    >
    </crud-footer>
@endsection