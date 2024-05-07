@extends('backoffice.main')

@section('page_title', $provincia->provincia . ' - General')

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
          action="{{ action('backoffice\ProvinciasController@destroy', ['id' => $provincia->id]) }}">
        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
    </form>

    <div class="nav-tabs-custom">
        
        @include('backoffice.provincias.tabs' , [ 'tab' => 'general' , 'idProvincia' => $provincia->id])

        <div class="tab-content">

            <div class="tab-pane active" id="general">

                <provincia-form :disabled="true" :provincia="{{ $provincia }}" edicion="{{ $edicion }}"></provincia-form>

                <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                    cancelar-url="/admin/configuracion/provincias"
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

