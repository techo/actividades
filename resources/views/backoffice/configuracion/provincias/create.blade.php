@extends('backoffice.main')

@section('page_title', 'Crear Provincia')


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