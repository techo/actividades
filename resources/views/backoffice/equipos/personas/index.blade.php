@extends('backoffice.main')

@section('page_title', 'Integrantes')

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.equipos.tabs' , [ 'tab' => 'personas' , 'idEquipo' => $idEquipo])
    
        <div class="tab-content">
            <div class="tab-pane active" id="personas">
                <div class="box">
                    <div class="box-body  with-border">
                        <equipo-personas-datatable
                            api-url="/admin/ajax/equipos/{{ $idEquipo }}/personas"
                            fields="{{ $fields }}"
                            idEquipo="{{ $idEquipo }}"
                            sort-order="{{ $sortOrder }}"
                            placeholder-text="Buscar por nombre, apellido o documento"
                            detail-url="/admin/equipos/"
                        ></equipo-personas-datatable>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </div>
            
@endsection