@extends('backoffice.main')

@section('page_title', 'Equipos')

@section('add-new')

    <span class="pull-right">
        <a href="/admin/equipos/crear" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> Crear Equipo
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
            <equipos-datatable
                    api-url="/admin/ajax/equipos/"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="Buscar por nombre o área"
                    detail-url="/admin/equipos/"
            ></equipos-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection