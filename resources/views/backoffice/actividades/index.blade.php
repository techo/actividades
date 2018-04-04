@extends('backoffice.main')

@section('page_title', 'Actividades')

@section('content')

    <div class="box">
        <div class="box-body  with-border">
            <datatable
                    api-url="/admin/ajax/actividades"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="Buscar por nombre, oficina, tipo o estado"
            ></datatable>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            The footer of the box
        </div>
        <!-- box-footer -->
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
