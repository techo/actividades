@extends('backoffice.main')

@section('page_title', $campana->nombre . ' - ' . __('backend.subscribed'))

@section('content')
    <div class="nav-tabs-custom">

        @include('backoffice.campanas.tabs', ['tab' => 'suscriptos'])

        <div class="tab-content">
            <div class="tab-pane active" id="suscriptos">

                {{-- Constructor de filtros genérico (campo · operador · valor). --}}
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
                        <filtros-listado list-key="suscriptos" context-id="{{ $campana->id }}"></filtros-listado>
                    </div>
                </div>

                <div class="box">
                    <div class="box-body with-border">
                        {{-- Pestañas de vistas guardadas + agrupación (mismo módulo). --}}
                        <vistas-listado list-key="suscriptos" context-id="{{ $campana->id }}"></vistas-listado>
                        <agrupar-listado list-key="suscriptos" context-id="{{ $campana->id }}"></agrupar-listado>
                        <campana-suscriptos-datatable
                            api-url="/admin/ajax/campanas/{{ $campana->id }}/suscriptos"
                            export-url="/admin/campanas/{{ $campana->id }}/exportar"
                            campana-id="{{ $campana->id }}"
                            fields="{{ $fields }}"
                            sort-order="{{ $sortOrder }}"
                        ></campana-suscriptos-datatable>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
