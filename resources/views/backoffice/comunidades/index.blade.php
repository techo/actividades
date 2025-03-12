@extends('backoffice.main')

@section('page_title', __('backend.comunidades'))

@section('add-new')

    <span class="pull-right">
        <a href="/admin/comunidades/crear" class="btn btn-primary btn-lg">
            <i class="fa fa-plus"></i> {{ __('backend.create_comunidad') }}
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
            <comunidades-datatable
                    api-url="/admin/ajax/comunidades"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="{{ __('backend.search_by_name_or_area') }}"
                    detail-url="/admin/comunidades/"
            ></comunidades-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection