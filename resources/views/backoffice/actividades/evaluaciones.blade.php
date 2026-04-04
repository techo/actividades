@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - ' . __('backend.evaluations'))

@section('content')
<div class="nav-tabs-custom">

    @include('backoffice.actividades.tabs' , [ 'tab' => 'evaluaciones' ])

    <div class="tab-content">

        <div class="tab-pane active" id="evaluaciones">
            <div class="row vertical-align">
                <div class="col-md-12">
                    <h3 class="pull-left">{{ __('backend.evaluations') }}</h3>
                    <span class="pull-right">
                        <br>
                        <a class="btn btn-primary" style="margin-right: 4px;" href="/actividades/{{$actividad->idActividad}}/evaluaciones" target="_blank">
                            <i class="fa fa-eye"></i>  {{ __('backend.view') }}
                        </a>
                        <btn-enviar-evaluaciones :id="{{$actividad->idActividad}}"></btn-enviar-evaluaciones>
                    </span>
                </div>
            </div>

            {{-- Fila 1: Resumen general (4 tarjetas) --}}
            <evaluaciones-general-stats :id="{{ $actividad->idActividad }}"></evaluaciones-general-stats>

            {{-- Fila 2: Evaluación General (histograma, NPS, comentarios) --}}
            <evaluaciones-actividad :id="{{ $actividad->idActividad }}"></evaluaciones-actividad>

            {{-- Fila 3: Tags - Atributos Destacados + Puntos de Mejora --}}
            <evaluaciones-tags-resumen :id="{{ $actividad->idActividad }}"></evaluaciones-tags-resumen>

            {{-- Fila 4: Competencias (radar) + Impacto Percibido --}}
            <div class="row">
                <div class="col-md-7">
                    <evaluaciones-competencias :id="{{ $actividad->idActividad }}"></evaluaciones-competencias>
                </div>
                <div class="col-md-5">
                    <evaluaciones-impacto :id="{{ $actividad->idActividad }}"></evaluaciones-impacto>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush
