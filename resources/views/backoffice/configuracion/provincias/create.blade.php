@extends('backoffice.main')

@section('page_title', __('backend.create_first_division'))


@section('content')
    <provincia-form
            edicion="{{ $edicion }}"
    ></provincia-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/configuracion/provincias"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection