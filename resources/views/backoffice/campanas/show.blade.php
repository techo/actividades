@extends('backoffice.main')

@section('page_title', $campana->nombre . ' - ' . __('backend.general'))

@section('content')
    <div class="nav-tabs-custom">

        @include('backoffice.campanas.tabs', ['tab' => 'general'])

        <div class="tab-content">
            <div class="tab-pane active" id="general">
                @php
                    $paisAdmin = \App\Pais::find(auth()->user()->idPaisPermitido);
                    $tipoSlug  = $campana->tipo ?: 'campania';
                    $publicUrl = $paisAdmin
                        ? url('/' . $paisAdmin->abreviacion . '/' . $tipoSlug . '/' . $campana->id)
                        : null;
                @endphp
                <campana-form
                    :campana-id="{{ $campana->id }}"
                    :initial-data="{{ json_encode([
                        'nombre'       => $campana->nombre,
                        'descripcion'  => $campana->descripcion,
                        'tipo'         => $campana->tipo,
                        'imagen'       => $campana->imagen,
                        'fecha_inicio' => $campana->fecha_inicio ? $campana->fecha_inicio->format('Y-m-d') : null,
                        'fecha_fin'    => $campana->fecha_fin    ? $campana->fecha_fin->format('Y-m-d')    : null,
                        'activa'       => $campana->activa,
                    ]) }}"
                    public-url="{{ $publicUrl }}"
                ></campana-form>
            </div>
        </div>

    </div>
@endsection
