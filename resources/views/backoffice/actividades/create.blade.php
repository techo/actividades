@extends('backoffice.main')

@section('page_title', 'Crear Actividad')

@section('subtitulo')

@endsection

@section('content')
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#general" data-toggle="tab" aria-expanded="true">General</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="general">
                <actividad></actividad>
                <crud-footer cancelar-url="/admin/actividades" edicion="{{ $edicion }}"></crud-footer>
            </div>
            <div class="tab-pane" id="grupos">
            </div>
            <div class="tab-pane" id="inscripciones">
            </div>
        </div>
    </div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush

@section('footer')
@endsection