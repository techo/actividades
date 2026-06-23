@extends('backoffice.main')

@section('page_title', $campana->nombre . ' - ' . __('backend.campaign_questions'))

@section('content')
    <div class="nav-tabs-custom">

        @include('backoffice.campanas.tabs', ['tab' => 'preguntas'])

        <div class="tab-content">
            <div class="tab-pane active" id="preguntas">
                <preguntas-manager base-url="/admin/ajax/campanas/{{ $campana->id }}/preguntas"></preguntas-manager>
            </div>
        </div>

    </div>
@endsection
