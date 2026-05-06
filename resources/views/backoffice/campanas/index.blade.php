@extends('backoffice.main')

@section('page_title', __('backend.campaigns'))

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif

    <div class="box">
        <div class="box-header with-border">
            <a href="/admin/campanas/crear" class="btn btn-primary">
                <i class="fa fa-plus"></i> {{ __('backend.create_campaign') }}
            </a>
        </div>
        <div class="box-body with-border">
            <campanas-datatable
                api-url="/admin/ajax/campanas"
                fields="{{ $fields }}"
                sort-order="{{ $sortOrder }}"
                placeholder-text="{{ __('backend.search') }}"
                detail-url="/admin/campanas/"
            ></campanas-datatable>
        </div>
    </div>
@endsection
