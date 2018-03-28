@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad)

@section('content')
    <actividades-show actividad="{{ $actividad }}"></actividades-show>
@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

@endpush

@push('additional_css')

@endpush
