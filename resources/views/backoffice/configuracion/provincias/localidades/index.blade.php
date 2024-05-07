@extends('backoffice.main')

@section('page_title', $provincia->provincia )

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.provincias.tabs' , [ 'tab' => 'localidades' , 'idProvincia' => $idProvincia])
    
        <div class="tab-content">
            <div class="tab-pane active" id="localidades">
                <div class="box">
                    <div class="box-body  with-border">
                        <localidades-datatable
                            api-url="/admin/ajax/configuracion/provincias/{{ $idProvincia }}/localidades"
                            fields="{{ $fields }}"
                            id-provincia="{{ $idProvincia }}"
                            sort-order="{{ $sortOrder }}"
                            placeholder-text="Buscar por nombre"
                            detail-url="/admin/configuracion/provincias/{{ $idProvincia }}/localidades"
                        ></localidades-datatable>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->

                <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                    cancelar-url="/admin/configuracion/provincias"
                ></crud-footer>
            </div>
        </div>
    </div>
            
@endsection