@extends('main')

@section('page_title')
    Mis Actividades
@endsection

@section('main_image')
@endsection

@section('main_content')
    <h1>Tus Actividades de Voluntariado</h1>
    <div class="col-md-4">
        <h3>Próximas Actividades</h3>
    </div>

    <mis-actividades></mis-actividades>
    <div class="col-md-4">
        <h3>Actividades Pasadas</h3>
    </div>
    <datatable
            api-url="/ajax/usuario/inscripciones?date=pasadas"
            fields="{{ $fields }}"
            sort-order="{{ $sortOrder }}"
            placeholder-text="Nombre o localidad de la actividad"
            id="datatable-mis-actividades"
    ></datatable>
    <p>&nbsp;</p>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
