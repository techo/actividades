@extends('backoffice.main')

@section('page_title', __('backend.create_team'))


@section('content')
    <equipo-form
            edicion="{{ $edicion }}"
    ></equipo-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/equipos"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection