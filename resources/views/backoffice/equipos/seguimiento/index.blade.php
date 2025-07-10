@extends('backoffice.main')

@section('page_title', $equipo->nombre . ' - ' . __('backend.seguimiento'))

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.equipos.tabs' , [ 'tab' => 'seguimiento' , 'idEquipo' => $idEquipo])
    
        <div class="tab-content">
            <div class="tab-pane active" id="personas">
                <div class="box">
                    <div class="box-body  with-border">
                        <reuniones-datatable
                            api-url="/admin/ajax/equipos/{{ $idEquipo }}/reuniones"
                            fields="{{ $fields }}"
                            id-equipo="{{ $idEquipo }}"
                            sort-order="{{ $sortOrder }}"
                        ></reuniones-datatable>
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