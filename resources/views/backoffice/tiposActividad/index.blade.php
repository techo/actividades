@extends('backoffice.main')

@section('page_title', __('backend.activity_type'))

@section('add-new')

    <span class="pull-right">
        <a href="/admin/configuracion/tipos-actividad/registrar" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> {{ __('backend.create_activity_type') }}
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
                    placeholder-text="{{__('backend.search_by_name')}}"
                    detail-url="/admin/configuracion/tipos-actividad/"
            ></tipos-actividad-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection