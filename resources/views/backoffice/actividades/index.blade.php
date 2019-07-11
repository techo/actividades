@extends('backoffice.main')

@section('page_title', 'Actividades')

@section('add-new')

    <span class="pull-right">
        <a href="/admin/actividades/crear" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> Nueva Actividad
        </a>
    </span>
@endsection

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
            @php
                \Illuminate\Support\Facades\Session::remove('mensaje');
            @endphp
        </div>
    @endif

    <div class="box">
        <div class="box-body with-border">
            <div class="table-responsive">
                <datatable
                        api-url="/admin/ajax/actividades"
                        fields="{{ $fields }}"
                        sort-order="{{ $sortOrder }}"
                        placeholder-text="Buscar por nombre, oficina o tipo"
                        detail-url="/admin/actividades/"
                ></datatable>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
@endpush

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endpush
