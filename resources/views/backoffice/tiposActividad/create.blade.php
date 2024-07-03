@extends('backoffice.main')

@section('page_title', __('backend.create_activity_type'))


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