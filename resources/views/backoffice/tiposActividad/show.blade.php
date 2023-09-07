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
                
                <crud-footer
                    cancelar-url="/admin/configuracion/tipos-actividad"
                    edicion="{{ $edicion }}"
                    can-editar="true"
                ></crud-footer>
            </div>
        </div>
</div>
@endsection