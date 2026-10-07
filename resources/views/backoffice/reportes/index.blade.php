@extends('backoffice.main')

@section('page_title', 'Reportes')

@section('content')
    <div class="box">
        <div class="box-body with-border">
            <reportes-datatable
                api-url="/admin/ajax/reportes"
                fields="{{ $fields }}"
                sort-order="{{ $sortOrder }}"
            ></reportes-datatable>
        </div>
    </div>
@endsection
