@extends('backoffice.main')

@section('page_title', __('backend.people'))

@section('add-new')

    <span class="pull-right">
        <a href="/admin/usuarios/registrar" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> {{ __('backend.create_person') }}
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
            <usuarios-datatable
                    api-url="/admin/ajax/usuarios/"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="{{ __('backend.search_by_name_lastName_document') }}"
                    detail-url="/admin/usuarios/"
            ></usuarios-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection