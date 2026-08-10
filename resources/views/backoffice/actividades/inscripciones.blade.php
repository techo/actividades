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
                    {{-- Filtros avanzados (campo · operador · valor) con chips, pegados
                         a la tabla: el botón despliega el constructor bajo demanda. --}}
                    <filtros-listado
                            list-key="inscripciones"
                            context-id="{{ $actividad->idActividad }}"
                    ></filtros-listado>
                    <inscripciones-table
                            ref="inscripcionestable"
                            api-url="{{ '/admin/ajax/actividades/' .$actividad->idActividad. '/inscripciones/'}}"
                            fields="{{ $fields }}"
                            sort-order="{{ $sortOrder }}"
                            placeholder-text="Buscar por DNI/Pasaporte, Nombre o Apellido"
                            actividad="{{$actividad->idActividad}}"
                            confirmacion="{{ $actividad->confirmacion }}"
                            pago="{{ $actividad->pago }}"
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