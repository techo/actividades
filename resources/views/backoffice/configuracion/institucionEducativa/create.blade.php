@extends('backoffice.main')

@section('page_title', __('backend.create') . ' ' . __('backend.educational_institution'))


@section('content')
    <institucion-educativa-form
            edicion="{{ $edicion }}"
    ></institucion-educativa-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/configuracion/provincias"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection