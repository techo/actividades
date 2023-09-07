@extends('backoffice.main')

@section('page_title', 'Oficina: ' . $oficina->nombre)


@section('content')

 <form method="POST" id="formDelete"
          action="{{ action('backoffice\ajax\OficinasController@delete', ['id' => $oficina->id]) }}">

        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
</form>

<div class="nav-tabs-custom">
        <div class="tab-content" style="background-color: #ECF0F1;">
            <div class="tab-pane active" id="general">

                <oficina-form
                    id="{{ $oficina->id }}"
                    edicion="{{ $edicion }}"
                ></oficina-form>

                <crud-footer
                    cancelar-url="/admin/configuracion/oficinas"
                    edicion="{{ $edicion }}"
                    can-editar="true"
                    can-borrar="{{Auth::user()->hasRole('admin')}}"
                ></crud-footer>

            </div>
        </div>
</div>
@endsection