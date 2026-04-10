@extends('backoffice.main')

@section('page_title', $campana->nombre)

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">{{ $campana->nombre }}</h3>
            @if($campana->descripcion)
                <p class="text-muted">{{ $campana->descripcion }}</p>
            @endif
        </div>
        <div class="box-body">

            {{-- Datos de la campaña (edición inline) --}}
            <campana-form
                :campana-id="{{ $campana->id }}"
                :initial-data="{{ json_encode([
                    'nombre'       => $campana->nombre,
                    'descripcion'  => $campana->descripcion,
                    'slug'         => $campana->slug,
                    'tipo'         => $campana->tipo,
                    'fecha_inicio' => $campana->fecha_inicio ? $campana->fecha_inicio->format('Y-m-d') : null,
                    'fecha_fin'    => $campana->fecha_fin    ? $campana->fecha_fin->format('Y-m-d')    : null,
                    'activa'       => $campana->activa,
                ]) }}"
            ></campana-form>

            <hr>

            {{-- Preguntas dinámicas --}}
            <h4>{{ __('backend.campaign_questions') }}</h4>
            <campana-preguntas
                :campana-id="{{ $campana->id }}"
            ></campana-preguntas>

            <hr>

            {{-- Suscriptos --}}
            <h4>{{ __('backend.subscribed') }}</h4>
            <p>
                @php $paisAdmin = \App\Pais::find(auth()->user()->idPaisPermitido); @endphp
                <a href="{{ $paisAdmin ? url('/' . $paisAdmin->abreviacion . '/campania/' . $campana->id) : '#' }}" target="_blank" class="btn btn-default btn-sm">
                    <i class="fa fa-external-link"></i> {{ __('backend.view_public_link') }}
                </a>
            </p>

            <campana-suscriptos-datatable
                api-url="/admin/ajax/campanas/{{ $campana->id }}/suscriptos"
                fields="{{ $fields }}"
                sort-order="{{ $sortOrder }}"
                campana-id="{{ $campana->id }}"
            ></campana-suscriptos-datatable>

        </div>
    </div>
@endsection
