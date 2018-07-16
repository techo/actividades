@extends('main')

@section('page_title')
    Mis Actividades
@endsection

@section('main_image')
@endsection

@section('main_content')
	{{--<mis-inscripciones></mis-inscripciones>--}}
    <datatable
            api-url="/ajax/usuario/inscripciones"
            fields="{{ $fields }}"
            sort-order="{{ $sortOrder }}"
            placeholder-text="Nombre o localidad de la actividad"
            detail-url="/actividades/"
    ></datatable>@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
