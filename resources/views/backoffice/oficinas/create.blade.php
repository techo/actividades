@extends('backoffice.main')

@section('page_title', 'Crear Oficina')


@section('content')
    <oficina-form
            edicion="{{ $edicion }}"
    ></oficina-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/configuracion/oficinas"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection