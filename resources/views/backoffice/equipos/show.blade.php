@extends('backoffice.main')

@section('page_title', $equipo->nombre . ' - ' . __('backend.general'))

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
          action="{{ action('backoffice\EquiposController@destroy', ['id' => $equipo->idEquipo]) }}">
        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
    </form>

    <div class="nav-tabs-custom">
        
        @include('backoffice.equipos.tabs' , [ 'tab' => __('backend.general') , 'idEquipo' => $equipo->idEquipo])

        <div class="tab-content">

            <div class="tab-pane active" id="general">

                <equipo-form :disabled="true" :equipo="{{ $equipo }}" edicion="{{ $edicion }}"></equipo-form>

                <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                    cancelar-url="/admin/equipos"
                    edicion="{{ $edicion }}"
                    can-editar="{{ 
                        Auth::user()->hasRole('admin')
                    }}"
                    can-borrar="{{
                        Auth::user()->hasRole('admin')
                    }}"
                ></crud-footer>
                
            </div>
    </div>

@endsection

