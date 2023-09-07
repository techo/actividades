@extends('backoffice.main')

@section('page_title', 'Persona: ' . $usuario->nombreCompleto)


@section('content')

 <form method="POST" id="formDelete"
          action="{{ action('backoffice\UsuariosController@delete', ['id' => $usuario->idPersona]) }}">

        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
</form>

<div class="nav-tabs-custom">
        <ul class="nav nav-tabs" style="border-bottom: 3px solid #d2d6de; padding-bottom: 2px;">
            <li class="active">
                <a href="#general" data-toggle="tab" aria-expanded="true">General</a>
            </li>
            <li>
                <a href="#inscripciones" data-toggle="tab" aria-expanded="true">Inscripciones</a>
            </li>
            <li>
                <a href="#ficha" data-toggle="tab" aria-expanded="true">Ficha Medica</a>
            </li>
            @role('admin')
            <li>
                <a href="#evaluaciones" data-toggle="tab" aria-expanded="true">Evaluaciones</a>
            </li>
            @endrole

            <li>
                <a href="#estudios" data-toggle="tab" aria-expanded="true">Estudios</a>
            </li>
        </ul>
        <div class="tab-content" style="background-color: #ECF0F1;">
            <div class="tab-pane active" id="general">

                <usuario-form
                    prop-usuario="{{ json_encode($arrUsuario) }}"
                    edicion="{{ $edicion }}"
                ></usuario-form>

                @role('admin')
                    <crud-footer
                            cancelar-url="/admin/usuarios"
                            edicion="{{ $edicion }}"
                            can-editar="true"
                            can-fusionar="true"
                            can-borrar="{{Auth::user()->hasPermissionTo('borrar_usuarios')}}"
                    ></crud-footer>
                @endrole
            </div>
            <div class="tab-pane" id="ficha">

                <usuario-ficha-tab
                    prop-usuario="{{ json_encode($ficha) }}"
                    edicion="{{ $edicion }}"
                ></usuario-ficha-tab>

            </div>
            <div class="tab-pane" id="inscripciones">

                <usuarios-inscripciones-tab persona="{{ $usuario->idPersona }}" > </usuarios-inscripciones-tab>

            </div>
            @role('admin')
            <div class="tab-pane" id="evaluaciones">

                <usuarios-evaluaciones-tab persona="{{ $usuario->idPersona }}" > </usuarios-evaluaciones-tab>

            </div>
            @endrole
            <div class="tab-pane" id="estudios">
                <usuarios-estudios-tab persona="{{ $usuario->idPersona }}" > </usuarios-estudios-tab>
            </div>
        </div>
@endsection
