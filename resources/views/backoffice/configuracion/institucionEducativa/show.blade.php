@extends('backoffice.main')

@section('page_title', $institucionEducativa->nombre . ' - General')

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
          action="{{ action('backoffice\InstitucionEducativaController@destroy', ['id' => $institucionEducativa->id]) }}">
        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
    </form>

    <div class="nav-tabs-custom">
        
        <div class="tab-content">

            <div class="tab-pane active" id="general">

                <institucion-educativa-form :disabled="true" :institucion-educativa="{{ $institucionEducativa }}" edicion="{{ $edicion }}"></institucion-educativa-form>

                <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                    cancelar-url="/admin/configuracion/institucionEducativa"
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

