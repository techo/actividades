@extends('backoffice.main')

@section('page_title', __('backend.subscribed'))


@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="box">
        <div class="box-body  with-border">
            <suscriptos-datatable
                    api-url="/admin/ajax/suscriptos/"
                    fields="{{ $fields }}"
                    sort-order="{{ $sortOrder }}"
                    placeholder-text="{{ __('backend.search_by_name_lastName_document') }}"
                    detail-url="/admin/usuarios/"
            ></suscriptos-datatable>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
@endsection