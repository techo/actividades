@extends('backoffice.main')

@section('page_title', $equipo->nombre . ' - ' . __('backend.members'))

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.equipos.tabs' , [ 'tab' => 'integrantes' , 'idEquipo' => $idEquipo])
    
        <div class="tab-content">
            <div class="tab-pane active" id="personas">
                {{-- Constructor de filtros genérico (mismo módulo que inscripciones/suscriptos). --}}
                <div class="box box-primary collapsed-box">
                    <div class="box-header with-border bg-success">
                        <h3 class="box-title">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                            {{ __('backend.advanced_search') }}
                        </h3>
                    </div>
                    <div class="box-body">
                        <filtros-listado list-key="integrantes" context-id="{{ $idEquipo }}"></filtros-listado>
                    </div>
                </div>

                <div class="box">
                    <div class="box-body  with-border">
                        {{-- Pestañas de vistas guardadas + agrupación (mismo módulo). --}}
                        <vistas-listado list-key="integrantes" context-id="{{ $idEquipo }}"></vistas-listado>
                        <agrupar-listado list-key="integrantes" context-id="{{ $idEquipo }}"></agrupar-listado>
                        <integrantes-datatable
                            api-url="/admin/ajax/equipos/{{ $idEquipo }}/integrante/estado/0"
                            fields="{{ $fields }}"
                            id-equipo="{{ $idEquipo }}"
                            sort-order="{{ $sortOrder }}"
                            placeholder-text="{{ __('backend.search_by_name_role_deployment_or_relationship') }}"
                            detail-url="/admin/equipos/"
                        ></integrantes-datatable>
                        <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                            cancelar-url="/admin/equipos"
                        ></crud-footer>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->

                
            </div>
        </div>
    </div>
            
@endsection