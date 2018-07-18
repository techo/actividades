@extends('main')

@section('page_title')
    Mis Actividades
@endsection

@section('main_image')
@endsection

@section('main_content')
	<mis-actividades></mis-actividades>
    <datatable
            api-url="/ajax/usuario/inscripciones?date=pasadas"
            fields="{{ $fields }}"
            sort-order="{{ $sortOrder }}"
            placeholder-text="Nombre o localidad de la actividad"
            detail-url="/actividades/"
    ></datatable>
    <p>&nbsp;</p>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
