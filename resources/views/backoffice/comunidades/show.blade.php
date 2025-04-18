@extends('backoffice.main')

@section('page_title', $comunidad->nombre . ' - ' . __('backend.general'))

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
          action="{{ action('backoffice\ajax\ComunidadesController@destroy', ['id' => $comunidad->idComunidad]) }}">
        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
    </form>

    <div class="nav-tabs-custom">
        @include('backoffice.comunidades.tabs' , [ 'tab' => 'general' , 'idComunidad' => $comunidad->idComunidad])

        <div class="tab-content">

            <div class="tab-pane active" id="general">

                <comunidad-form :disabled="true" :comunidad="{{ $comunidad }}" edicion="{{ $edicion }}"></comunidad-form>

                <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                    cancelar-url="/admin/comunidades"
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

