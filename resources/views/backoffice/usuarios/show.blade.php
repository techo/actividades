@extends('backoffice.main')

@section('page_title', 'Usuario: ' . $usuario->nombreCompleto)


@section('content')
<div class="nav-tabs-custom">
        <ul class="nav nav-tabs" style="border-bottom: 3px solid #d2d6de; padding-bottom: 2px;">
            <li class="active">
                <a href="#general" data-toggle="tab" aria-expanded="true">General</a>
            </li>
            <li>
                <a href="#inscripciones" data-toggle="tab" aria-expanded="true">Inscripciones</a>
            </li>
            <li>
                <a href="#evaluaciones" data-toggle="tab" aria-expanded="true">Evaluaciones</a>
            </li>
        </ul>
        <div class="tab-content" style="background-color: #ECF0F1;">
            <div class="tab-pane active" id="general">

                <usuario-form
                    prop-usuario="{{ json_encode($arrUsuario) }}"
                    edicion="{{ $edicion }}"
                ></usuario-form>

            </div>
            <div class="tab-pane" id="inscripciones">

                <usuarios-inscripciones-tab persona="{{ $usuario->idPersona }}" > </usuarios-inscripciones-tab>

            </div>
            <div class="tab-pane" id="evaluaciones">

                <usuarios-evaluaciones-tab persona="{{ $usuario->idPersona }}" > </usuarios-evaluaciones-tab>

            </div>
        </div>
        <br/>
        <br/>
@endsection

@section('footer')
    <crud-footer
            cancelar-url="/admin/usuarios"
            edicion="{{ $edicion }}"
            can-editar="true"
    ></crud-footer>
@endsection