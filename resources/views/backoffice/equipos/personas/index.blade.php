@extends('backoffice.main')

@section('page_title', $equipo->nombre . ' - Integrantes')

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
                        <integrantes-datatable
                            api-url="/admin/ajax/equipos/{{ $idEquipo }}/integrante"
                            fields="{{ $fields }}"
                            id-equipo="{{ $idEquipo }}"
                            sort-order="{{ $sortOrder }}"
                            placeholder-text="Buscar por nombre, rol, despliegue o relación"
                            detail-url="/admin/equipos/"
                        ></integrantes-datatable>
                        <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                            cancelar-url="/admin/equipos"
                        ></crud-footer>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->

                
            </div>
        </div>
    </div>
            
@endsection