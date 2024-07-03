@extends('backoffice.main')

@section('page_title', __('backend.first_geo_division'))

@section('add-new')

    <span class="pull-right">
        <a href="/admin/configuracion/provincias/crear" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> {{ __('backend.create_first_division') }}
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
            <provincias-datatable
                    api-url="/admin/ajax/configuracion/provincias/"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="{{ __('backend.search_by_name') }}"
                    detail-url="/admin/configuracion/provincias/"
            ></provincias-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection