@extends('backoffice.main')

@section('page_title', 'Nueva Actividad')

@section('subtitulo')

@endsection

@section('content')
    <actividades-show
            actividad="{{ $actividad }}"
            paises="{{ $paises }}"
            localidades=""
            provincias=""
            tipos=""
            categorias="{{ $categorias }}"
            edicion="{{ $edicion }}"
    ></actividades-show>
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