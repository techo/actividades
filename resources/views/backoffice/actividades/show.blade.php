@extends('backoffice.main')

@section('page_title', $actividad->nombreActividad)

@section('subtitulo')
    Ultima modificación por
    {{ $actividad->modificadoPor->nombres OR 'N/A' }}
    {{ $actividad->modificadoPor->apellidoPaterno OR '' }}
    el {{ $actividad->fechaModificacion->format('Y-m-d') }}
@endsection

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

    <actividades-show
            actividad="{{ $actividad }}"
            paises="{{ $paises }}"
            tipos="{{ $tipos }}"
            categorias="{{ $categorias }}"
            provincias="{{  $provincias }}"
            localidades="{{ $localidades }}"
            edicion={{ $edicion }}
    ></actividades-show>
    <p style="margin-bottom: 4em"></p>
@endsection

@push('additional_scripts')
    <script src="{{ asset('/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
@endpush

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
@endpush

@section('footer')
    <crud-footer
            cancelar-url="/admin/actividades"
            edicion="{{ $edicion }}"
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
    ></crud-footer>
@endsection