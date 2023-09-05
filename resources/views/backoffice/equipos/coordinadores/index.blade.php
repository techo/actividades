@extends('backoffice.main')

@section('page_title', $equipo->nombre . ' - Coordinadores')

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.equipos.tabs' , [ 'tab' => 'coordinadores' , 'idEquipo' => $idEquipo])
    
        <div class="tab-content">
            <div class="tab-pane active" id="coordinadores">
                <coordinadores-equipo />
            </div>
        </div>
    </div>
            
@endsection