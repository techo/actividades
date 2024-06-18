@extends('backoffice.main')

@section('page_title', 'Institución Educativa')

@section('add-new')

    <span class="pull-right">
        <a href="/admin/configuracion/institucionEducativa/crear" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> Crear Institución Educativa
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
            <institucion-educativa-datatable
                    api-url="/admin/ajax/configuracion/institucionEducativa/"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="Buscar por nombre"
                    detail-url="/admin/configuracion/institucionEducativa/"
            ></institucion-educativa-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection