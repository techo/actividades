@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - puntos')

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'inscripciones' ])

    <div class="tab-content">
        <div class="tab-pane active" id="inscripciones">
            
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

                <div class="box-body">
                    <filtros-inscripciones
                            campos="{{ $camposInscripciones }}"
                            condiciones="{{ $condiciones }}"
                            ref="filtro"
                    ></filtros-inscripciones>
                </div>

                <div class="box-footer">
                    <condiciones-seleccionadas></condiciones-seleccionadas>
                </div>

            </div>

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

            </div>

        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush