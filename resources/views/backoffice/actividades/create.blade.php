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
@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
@endpush

@push('additional_css')

@endpush

@section('footer')
    <crud-footer
            cancelar-url="/admin/actividades"
            edicion="{{ $edicion }}"
    >
    </crud-footer>
@endsection