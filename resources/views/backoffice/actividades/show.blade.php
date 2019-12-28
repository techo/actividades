@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad)

@section('content')
    @if (Session::has('error'))
        <div class="callout callout-danger">
            <h4>{{ Session::get('error') }}</h4>
            @php
                \Illuminate\Support\Facades\Session::remove('error');
            @endphp
        </div>
    @endif

    <modal-auditoria></modal-auditoria>

    <form method="POST" id="formDelete"
          action="{{ action('backoffice\ActividadesController@destroy', ['id' => $actividad->idActividad]) }}">
        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
    </form>

    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#general" data-toggle="tab" aria-expanded="true">General</a>
            </li>
            <li class="">
                <a href="#puntos" data-toggle="tab" aria-expanded="true">Puntos de encuentro</a>
            </li>
            <li>
                <a href="#inscripciones" data-toggle="tab" aria-expanded="true">Inscripciones</a>
            </li>
            <li>
                <a href="#grupos" data-toggle="tab" aria-expanded="true">Grupos</a>
            </li>
            <li>
                <a href="#evaluaciones" data-toggle="tab" aria-expanded="true">Evaluaciones</a>
            </li>
            <li>
                <a href="#coordinadores" data-toggle="tab" aria-expanded="true">Coordinadores</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="general">
                <actividad id="{{ $actividad->idActividad }}"></actividad>
                <crud-footer
                    cancelar-url="/admin/actividades/usuario"
                    edicion="{{ $edicion }}"
                    compartir="{{ $compartir }}"
                    can-editar="{{ Auth::user()->hasPermissionTo('editar_actividad') &&
                    (
                        ($actividad->idPersonaModificacion == Auth::user()->idPersona ||
                            $actividad->idCoordinador == Auth::user()->idPersona
                        ) ||
                        Auth::user()->hasRole('admin')
                    ) }}"
                    can-borrar="{{Auth::user()->hasPermissionTo('borrar_actividad') &&
                    (
                        ($actividad->idPersonaModificacion == Auth::user()->idPersona ||
                            $actividad->idCoordinador == Auth::user()->idPersona
                        ) ||
                        Auth::user()->hasRole('admin')
                    )}}"
                    can-clonar="true"
                ></crud-footer>
            </div>
            <div class="tab-pane" id="grupos">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Incluir en este Grupo</h3>
                    </div>
                    <div class="box-body">
                        <btn-grupo-persona
                                actividad="{{ $actividad }}"
                        ></btn-grupo-persona>

                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <miembros
                                actividad="{{ $actividad }}"
                                items = "{{ json_encode($miembros) }}"
                                id-grupo-raiz = "{{ $miembros['idRaiz'] }}"
                        >
                        </miembros>
                    </div>
                    <div class="box-body">

                        <div class="row">
                            <div class="col-md-12">
                                <miembros-tabla
                                        api-url={{ '/admin/ajax/grupos/'. $miembros['idRaiz'] .'/miembros' }}
                                        fields="{{ $fieldsMiembros }}"
                                        sort-order = "{{ $sortOrderMiembros }}"
                                        placeholder-text="Buscar por Nombre o Rol"
                                        id-grupo-raiz = "{{ $miembros['idRaiz'] }}"
                                        id-actividad = "{{ $actividad->idActividad }}"
                                        ref="miembrosTabla"
                                ></miembros-tabla>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="tab-pane" id="puntos">
                <puntos id="{{ $actividad->idActividad }}" fields="{{ $fieldsPuntos }}" sort-order="{{ $sortOrderPuntos }}" ></puntos>
            </div>

            <div class="tab-pane" id="inscripciones">
                <div class="box box-primary collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Búsqueda avanzada</h3>
                        <div class="box-tools pull-right">
                            <!-- Collapse Button -->
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <filtros-inscripciones
                                campos="{{ $camposInscripciones }}"
                                condiciones="{{ $condiciones }}"
                                ref="filtro"
                        ></filtros-inscripciones>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <condiciones-seleccionadas></condiciones-seleccionadas>
                    </div>
                    <!-- box-footer -->
                </div>
                <!-- /.box -->
                <div class="box">
                    <div class="box-body  with-border">
                        <inscripciones-mensajes></inscripciones-mensajes>
                        <inscripciones-table
                                ref="inscripcionestable"
                                api-url="{{ '/admin/ajax/actividades/' .$actividad->idActividad. '/inscripciones/'}}"
                                fields="{{ $fields }}"
                                sort-order="{{ $sortOrder }}"
                                placeholder-text="Buscar por DNI/Pasaporte, Nombre o Apellido"
                                actividad="{{$actividad->idActividad}}"
                        ></inscripciones-table>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <div class="tab-pane" id="evaluaciones">
                <div class="row vertical-align">
                    <div class="col-md-12">
                        <h3 class="pull-left">Evaluaciones</h3>
                        <span class="pull-right">
                            <br>
                            <btn-enviar-evaluaciones :prop-actividad="{{$actividad}}"></btn-enviar-evaluaciones>
                        </span>
                    </div>
                </div>
                <evaluaciones-general-stats></evaluaciones-general-stats>
                <evaluaciones-actividad></evaluaciones-actividad>
                <evaluaciones-voluntarios></evaluaciones-voluntarios>
            </div>

            <div class="tab-pane" id="coordinadores">
                <accesos :id="{{ $actividad->idActividad }}"></accesos>
            </div>
        </div>
    </div>

    @include('backoffice.partials.compartir-modal', ['url' => action('ActividadesController@show', ['id' => $actividad->idActividad]), 'title' => $actividad->nombreActividad])

@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
@endpush

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush

@section('footer')
@endsection


@push('addiitional_css')
    <link rel="stylesheet" href="{{ asset('/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endpush
