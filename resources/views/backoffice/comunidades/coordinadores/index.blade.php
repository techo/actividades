@extends('backoffice.main')

@section('page_title', $comunidad->nombre . ' - ' . __('backend.coordination'))

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.comunidades.tabs' , [ 'tab' => 'coordinadores' , 'idComunidad' => $idComunidad])
    
        <div class="tab-content">
            <div class="tab-pane active" id="coordinadores">
                <coordinadores-comunidad
                    comunidad="{{ $comunidad }}"
                    id = "{{ $idComunidad }}"
                />
            </div>
        </div>
    </div>
            
@endsection