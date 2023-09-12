@extends('backoffice.main')

@section('page_title', $provincia->nombre . ' - Localidades')

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.provincias.tabs' , [ 'tab' => 'localidad' , 'idProvincia' => $id])
    
        <div class="tab-content">
            <div class="tab-pane active" id="localidades">
                <div class="box">
                    <div class="box-body  with-border">
                        <localidades-datatable
                            api-url="/admin/ajax/configuracion/provincias/{{ $id }}/integrante"
                            fields="{{ $fields }}"
                            id-equipo="{{ $id }}"
                            sort-order="{{ $sortOrder }}"
                            placeholder-text="Buscar por nombre"
                            detail-url="/admin/configuracion/provincias/"
                        ></localidades-datatable>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </div>
            
@endsection