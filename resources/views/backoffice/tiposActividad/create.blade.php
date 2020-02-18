@extends('backoffice.main')

@section('page_title', 'Crear Tipo de Actividad')


@section('content')
    <tipos-actividad-form
            edicion="{{ $edicion }}"
    ></tipos-actividad-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/configuracion/tipos-actividad"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection