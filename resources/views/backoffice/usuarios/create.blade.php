@extends('backoffice.main')

@section('page_title', 'Registrar usuario')


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