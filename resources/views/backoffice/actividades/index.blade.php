@extends('backoffice.main')

@section('page_title', 'Actividades')

@section('content')

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Default Box Example</h3>
            <div class="box-tools pull-right">
                <!-- Buttons, labels, and many other things can be placed here! -->
                <!-- Here is a label for example -->
                <span class="label label-primary">Label</span>
            </div>
            <!-- /.box-tools -->
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            {{--<datatable encabezados="{{ $encabezados }}" url="/admin/ajax/actividades"></datatable>--}}
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
    <script>
        $(document).ready(function () {
            $(document).ready(function() {
                // var oTable = $('#tabla').DataTable({
                //     "processing": true,
                //     "serverSide": true,
                //     "ajax": '/admin/ajax/actividades',
                //     "columns": [
                //         {data: 'idActividad', name: 'idActividad'},
                //         {data: 'nombreActividad', name: 'nombreActividad'},
                //         {data: 'fechaInicio', name: 'fechaInicio'},
                //         {data: 'fechaFin', name: 'fechaFin'},
                //         {data: 'idUnidadOrganizacional', name: 'idUnidadOrganizacional'},
                //         {data: 'nombreUnidadOrganizacional', name: 'nombreUnidadOrganizacional'}
                //     ]
                // });
            });
        });
    </script>
@endpush

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endpush
