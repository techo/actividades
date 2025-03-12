@extends('backoffice.main')

@section('page_title', __('backend.create_comunidad'))


@section('content')
    <comunidad-form
            edicion="{{ $edicion }}"
    ></comunidad-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/comunidades"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection