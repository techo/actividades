@extends('backoffice.main')

@section('page_title', 'Header')


@section('content')
    <home-header-form
            edicion="{{ $edicion }}"
            home-header="{{ $homeHeader }}"
    ></home-header-form>
@endsection

@section('footer')
    <crud-footer
            edicion="{{ $edicion }}"
    ></crud-footer>
@endsection