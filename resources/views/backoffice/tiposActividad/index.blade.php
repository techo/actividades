@extends('backoffice.main')

@section('page_title', 'Tipos de Actividad')

@section('add-new')

    <span class="pull-right">
        <a href="/admin/configuracion/tipos-actividad/registrar" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> Crear Tipo de Actividad
        </a>
    </span>
@endsection

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="box">
        <div class="box-body  with-border">
            <tipos-actividad-datatable
                    api-url="/admin/ajax/configuracion/tipos-actividad"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="Buscar por nombre"
                    detail-url="/admin/configuracion/tipos-actividad/"
            ></tipos-actividad-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection