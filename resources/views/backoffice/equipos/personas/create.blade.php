@extends('backoffice.main')

@section('page_title', __('backend.add_member'))


@section('content')
    <equipo-persona-form
            edicion="{{ $edicion }}"
    ></equipo-persona-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/equipos/{{ $idEquipo }}/personas"
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection