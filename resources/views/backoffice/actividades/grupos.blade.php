@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - Grupos')

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'grupos' ])

    <div class="tab-content">

        <div class="tab-pane active" id="grupos">

            <div class="box box-primary">

                <div class="box-header with-border">
                    <h3 class="box-title">Incluir en este Grupo</h3>
                </div>

                <div class="box-body">
                    <btn-grupo-persona actividad="{{ $actividad }}" ></btn-grupo-persona>
                </div>

            </div>

            <div class="box box-primary">

                <div class="box-header with-border">
                    <miembros actividad="{{ $actividad }}"items = "{{ json_encode($miembros) }}"id-grupo-raiz = "{{ $miembros['idRaiz'] }}"></miembros>
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

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush