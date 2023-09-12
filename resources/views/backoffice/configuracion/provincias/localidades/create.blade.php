@extends('backoffice.main')

@section('page_title', 'Agregar Localidad')


@section('content')
    <localidad-form
            edicion="{{ $edicion }}"
    ></localidad-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/provincias/{{ $id }}/localidades"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection