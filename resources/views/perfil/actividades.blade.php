@extends('main')

@section('page_title')
    {{ __('frontend.my_activities') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
    <h1>{{ __('frontend.my_activities') }}</h1>
    <div class="col-md-4">
        <h3>{{ __('frontend.next_activities') }} </h3>
    </div>

    <mis-actividades></mis-actividades>
    <div class="col-md-4">
        <h3>{{ __('frontend.past_activities') }} </h3>
    </div>
    <datatable
            api-url="/ajax/usuario/inscripciones?date=pasadas"
            fields="{{ $fields }}"
            sort-order="{{ $sortOrder }}"
            v-bind:placeholder-text="$t('frontend.filter_placeholder')"
            id="datatable-mis-actividades"
            track-by="idActividad"
    ></datatable>
    <p>&nbsp;</p>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
