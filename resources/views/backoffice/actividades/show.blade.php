@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - General')

@section('content')
    @if (Session::has('error'))
        <div class="callout callout-danger">
            <h4>{{ Session::get('error') }}</h4>
            @php
                \Illuminate\Support\Facades\Session::remove('error');
            @endphp
        </div>
    @endif

    <form method="POST" id="formDelete"
          action="{{ action('backoffice\ActividadesController@destroy', ['id' => $actividad->idActividad]) }}">
        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
    </form>

    <div class="nav-tabs-custom">
        
        @include('backoffice.actividades.tabs' , [ 'tab' => 'general' ])

        <div class="tab-content">

            <div class="tab-pane active" id="general">

                <actividad :disabled="true" id="{{ $actividad->idActividad }}"></actividad>

                <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                    cancelar-url="/admin/actividades/usuario"
                    edicion="{{ $edicion }}"
                    compartir="{{ $compartir }}"
                    can-editar="{{ Auth::user()->hasPermissionTo('editar_actividad') &&
                    (
                        ($actividad->idPersonaModificacion == Auth::user()->idPersona ||
                            $actividad->idCoordinador == Auth::user()->idPersona
                        ) ||
                        Auth::user()->hasRole('admin')
                    ) }}"
                    can-borrar="{{Auth::user()->hasPermissionTo('borrar_actividad') &&
                    (
                        ($actividad->idPersonaModificacion == Auth::user()->idPersona ||
                            $actividad->idCoordinador == Auth::user()->idPersona
                        ) ||
                        Auth::user()->hasRole('admin')
                    )}}"
                    can-clonar="true"
                ></crud-footer>
                
            </div>
    </div>

    @include('backoffice.partials.compartir-modal', ['url' => action('ActividadesController@show', ['id' => $actividad->idActividad]), 'title' => $actividad->nombreActividad])

@endsection

