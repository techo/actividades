@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad)

@section('subtitulo')
    Ultima modificación por
    {{ $actividad->modificadoPor->nombres OR 'N/A' }}
    {{ $actividad->modificadoPor->apellidoPaterno OR '' }}
@endsection

@section('content')
    <actividades-show
            actividad="{{ $actividad }}"
            paises="{{ $paises }}"
            provincias="{{  $provincias }}"
            localidades="{{ $localidades }}"
            edicion="{{ $edicion }}"
    ></actividades-show>
@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
@endpush

@push('additional_css')

@endpush

@section('footer')
    <crud-footer></crud-footer>
@endsection