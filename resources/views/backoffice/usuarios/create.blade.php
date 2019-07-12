@extends('backoffice.main')

@section('page_title', 'Crear Persona')


@section('content')
    <usuario-form
            edicion="{{ $edicion }}"
    ></usuario-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/usuarios"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection