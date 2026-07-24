@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - Inscripciones')

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'inscripciones' ])

    <div class="tab-content">
        <div class="tab-pane active" id="inscripciones">
            @if(isset($inscripcion))
                    <confirmar-presente
                            inscripcion="{{ $inscripcion }}"
                            persona="{{ $persona }}"
                    ></confirmar-presente>
            @endif
            <div class="box box-primary collapsed-box">

                <div class="box-header with-border bg-success">
                    <h3 class="box-title">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                        {{  __('backend.advanced_search') }}
                    </h3>
                </div>

                <div class="box-body">
                    {{-- Constructor de filtros genérico (campo · operador · valor) con
                         preview de coincidencias y chips. Reutilizable por listado. --}}
                    <filtros-listado
                            list-key="inscripciones"
                            context-id="{{ $actividad->idActividad }}"
                    ></filtros-listado>
                </div>

            </div>

            <div class="box">

                <div class="box-body  with-border">
                    <inscripciones-mensajes></inscripciones-mensajes>
                    {{-- Pestañas de vistas guardadas (predefinidas + propias). --}}
                    <vistas-listado
                            list-key="inscripciones"
                            context-id="{{ $actividad->idActividad }}"
                    ></vistas-listado>
                    {{-- Selector "Agrupar por" + fila de recuento por grupo (facets). --}}
                    <agrupar-listado
                            list-key="inscripciones"
                            context-id="{{ $actividad->idActividad }}"
                    ></agrupar-listado>
                    <inscripciones-table
                            ref="inscripcionestable"
                            api-url="{{ '/admin/ajax/actividades/' .$actividad->idActividad. '/inscripciones/'}}"
                            fields="{{ $fields }}"
                            sort-order="{{ $sortOrder }}"
                            placeholder-text="Buscar por DNI/Pasaporte, Nombre o Apellido"
                            actividad="{{$actividad->idActividad}}"
                    ></inscripciones-table>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush