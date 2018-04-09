@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad)

@section('subtitulo')
    Ultima modificación por
    {{ $actividad->modificadoPor->nombres }} {{ $actividad->modificadoPor->apellidoPaterno }}
@endsection

@section('content')
    <actividades-show
            actividad="{{ $actividad }}"
            paises="{{ $paises }}"
            edicion="{{ $edicion }}"
    ></actividades-show>
@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
@endpush

@push('additional_css')

@endpush

@section('footer')
    {{--
        <footer class="main-footer" style="position:fixed; bottom: 0; width: 100%">
            <!-- To the right -->
            <div style="margin-left: 75%">
                <button class="btn btn-primary">Editar</button>
            </div>
        </footer>
    --}}
    <crud-footer></crud-footer>
@endsection