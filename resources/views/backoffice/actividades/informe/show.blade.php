@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad . ' - ' . __('backend.informe_cierre'))

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
        
        @include('backoffice.actividades.tabs' , [ 'tab' => 'informe_cierre'])

        <div class="tab-content">

            <div class="tab-pane active" id="general">

                <actividad-informe-cierre-form :disabled="true" :actividad="{{ $actividad }}" :informe="{{ $informe }}" edicion="{{ $edicion }}"></actividad-informe-cierre-form>

            </div>
    </div>

@endsection

