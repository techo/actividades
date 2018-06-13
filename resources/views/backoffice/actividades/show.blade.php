@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad)

@section('subtitulo')
    Ultima modificación por
    {{ $actividad->modificadoPor->nombres OR 'N/A' }}
    {{ $actividad->modificadoPor->apellidoPaterno OR '' }}
    el {{ $actividad->fechaModificacion->format('d-m-Y') }}
@endsection

@section('content')
    @if (Session::has('error'))
        <div class="callout callout-danger">
            <h4>{{ Session::get('error') }}</h4>
            @php
                \Illuminate\Support\Facades\Session::remove('error');
            @endphp
        </div>
    @endif

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
            <li>
                <a href="#grupos" data-toggle="tab" aria-expanded="true">Grupos</a>
            </li>
            <li>
                <a href="#inscripciones" data-toggle="tab" aria-expanded="true">Inscripciones</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="general">
                <actividades-show
                        actividad="{{ $actividad }}"
                        paises="{{ $paises }}"
                        tipos="{{ $tipos }}"
                        categorias="{{ $categorias }}"
                        provincias="{{  $provincias }}"
                        localidades="{{ $localidades }}"
                        edicion="{{ $edicion }}"
                        ref="actividad"
                ></actividades-show>
            </div>
            <div class="tab-pane" id="grupos">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Buscar</h3>
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
                                        placeholder-text="Buscar por nombre, oficina, tipo o estado"
                                        id-grupo-raiz = "{{ $miembros['idRaiz'] }}"
                                        id-actividad = {{ $actividad->idActividad }}
                                        ref="miembrosTabla"
                                ></miembros-tabla>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="tab-pane" id="inscripciones">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Buscar</h3>
                        <div class="box-tools pull-right">
                            <!-- Collapse Button -->
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
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
                                placeholder-text="Buscar por cualquier campo"
                                actividad="{{$actividad->idActividad}}"
                        ></inscripciones-table>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </div>
    <p style="margin-bottom: 4em"></p>
    @include('backoffice.partials.compartir-modal', ['url' => action('ActividadesController@show', ['id' => $actividad->idActividad]), 'title' => $actividad->nombreActividad])
@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
    <script src="{{ asset('/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script>
        new ClipboardJS('#copiar_url');

        function mostrarTooltip(){
            $("#copiar_url").tooltip({trigger: 'manual'});
            $("#copiar_url").tooltip('show');
        }
    </script>
@endpush

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush

@section('footer')
    <crud-footer
            cancelar-url="/admin/actividades"
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
    ></crud-footer>
@endsection

@push('additional_scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endpush

@push('addiitional_css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="{{ asset('/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endpush