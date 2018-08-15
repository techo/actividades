@extends('backoffice.main')

@section('page_title', 'Usuario: ' . $usuario->nombreCompleto)


@section('content')
    <usuario-form
        prop-usuario="{{ json_encode($arrUsuario) }}"
        edicion="{{ $edicion }}"
    ></usuario-form>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/usuarios"
            edicion="{{ $edicion }}"
            can-editar="true"
    ></crud-footer>
@endsection