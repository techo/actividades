@extends('backoffice.main')

@section('page_title', __('backend.offices'))

@section('add-new')

    <span class="pull-right">
        <a href="/admin/configuracion/oficinas/registrar" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> {{ __('backend.create') }} {{__('backend.office')}}
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
            <oficinas-datatable
                    api-url="/admin/ajax/configuracion/oficinas"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="{{__('backend.search_by_name')}}"
                    detail-url="/admin/configuracion/oficinas/"
            ></oficinas-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection