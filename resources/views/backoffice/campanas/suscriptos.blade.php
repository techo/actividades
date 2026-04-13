@extends('backoffice.main')

@section('page_title', $campana->nombre . ' - ' . __('backend.subscribed'))

@section('content')
    <div class="nav-tabs-custom">

        @include('backoffice.campanas.tabs', ['tab' => 'suscriptos'])

        <div class="tab-content">
            <div class="tab-pane active" id="suscriptos">

                <div class="box">
                    <div class="box-body with-border">
                        <campana-suscriptos-datatable
                            api-url="/admin/ajax/campanas/{{ $campana->id }}/suscriptos"
                            export-url="/admin/campanas/{{ $campana->id }}/exportar"
                            campana-id="{{ $campana->id }}"
                        ></campana-suscriptos-datatable>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
