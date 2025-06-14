@extends('backoffice.main')

@section('page_title', $comunidad->nombre . ' - ' . __('comunidad_ficha_inicial.ficha'))

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
          action="">
        <input type="hidden" value="DELETE" name="_method">
        {{ csrf_field() }}
    </form>

    <div class="nav-tabs-custom">
        
        @include('backoffice.comunidades.tabs' , [ 'tab' => __('comunidad_ficha_inicial.ficha') , 'idComunidad' => $comunidad->idComunidad])

        <div class="tab-content">

            <div class="tab-pane active" id="general">

                <ficha-comunidad-form :disabled="true" :comunidad="{{ $comunidad }}" :ficha='@json($ficha)' edicion="{{ $edicion }}"></ficha-comunidad-form>

                <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                    edicion="{{ $edicion }}"
                    can-editar="{{ 
                        Auth::user()->hasRole('admin')
                    }}"
                ></crud-footer>
                
            </div>
    </div>

@endsection

