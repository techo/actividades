@extends('backoffice.main')

@section('page_title', $comunidad->nombre . ' - ' . __('backend.activities'))

@section('content')
    @if (Session::has('mensaje'))
        <div class="callout callout-success">
            <h4>{{ Session::get('mensaje') }}</h4>
        </div>
    @endif
    <div class="nav-tabs-custom">
        @include('backoffice.comunidades.tabs' , [ 'tab' => 'personas' , 'idComunidad' => $idComunidad])
    
        <div class="tab-content">
            <div class="tab-pane active" id="actividades-comunidad">
                <div class="box">
                    <div class="box-body  with-border">
                    <datatable
                        api-url="/admin/ajax/comunidades/{{ $idComunidad }}/actividades"
                        fields="{{ $fields }}"
                        sort-order="{{ $sortOrder }}"
                        placeholder-text="{{ __('backend.search_by_name_office_or_type') }}"
                        detail-url="/admin/actividades/"
                    ></datatable>
                        <crud-footer style="position: fixed;bottom: 0px;width: 80%;margin-left: 0px;"
                            cancelar-url="/admin/comunidades"
                        ></crud-footer>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->

                
            </div>
        </div>
    </div>
            
@endsection