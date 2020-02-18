@extends('backoffice.main')

@section('page_title', 'Tipo de Actividad: ' . $tipoActividad->nombre)


@section('content')

 <form method="POST" id="formDelete"
          action="{{ action('backoffice\ajax\TiposActividadController@delete', ['id' => $tipoActividad->idTipo]) }}">

        <input type="hidden" value="DELETE" name="_method">

        {{ csrf_field() }}
</form>

<div class="nav-tabs-custom">
        <div class="tab-content" style="background-color: #ECF0F1;">
            <div class="tab-pane active" id="general">

                <tipos-actividad-form
                    id="{{ $tipoActividad->idTipo }}"
                    edicion="{{ $edicion }}"
                ></tipos-actividad-form>

            </div>
        </div>
</div>
@endsection

@section('footer')
    @role('admin')
    <crud-footer
            cancelar-url="/admin/configuracion/tipos-actividades"
            edicion="{{ $edicion }}"
            can-editar="true"
            can-borrar="{{Auth::user()->hasRole('admin')}}"
    ></crud-footer>
    @endrole
@endsection