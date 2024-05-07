@extends('backoffice.main')

@section('page_title', 'Agregar Segunda División')


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