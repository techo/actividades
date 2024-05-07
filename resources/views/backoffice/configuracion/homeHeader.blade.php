@extends('backoffice.main')

@section('page_title', 'Header')


@section('content')
    <home-header-form
        edicion="{{ $edicion }}"
        id-home-header="{{ $homeHeader->idHomeHeader }}"
        header="{{ $homeHeader->header }}"
        sub-header="{{ $homeHeader->subHeader }}"
        imagen="{{ $homeHeader->imagen }}"
    ></home-header-form>
@endsection

@section('footer')
    <crud-footer
        cancelar-url="/admin/configuracion/home-header"
        edicion="{{ $edicion }}"
        can-editar="true"
    ></crud-footer>
@endsection