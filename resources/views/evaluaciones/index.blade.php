@extends('main')

@section('page_title')
    Evaluaciones de {{ $actividad->nombreActividad }}
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <h2 class="text-uppercase">{{ __('frontend.index_actividades_text') }}</h2>
    </div>
@endsection

@section('main_content')
    <h1>{{ __('frontend.feedback_of') }} {{ $actividad->nombreActividad }}</h1>
    <evaluar-actividad prop-actividad="{{ $actividad }}" respuesta="{{ $respuestaActividad }}"></evaluar-actividad>
    <contenedor-evaluaciones
            prop-actividad="{{ $actividad }}"
            prop-user="{{ auth()->user() }}"
            prop-inscriptos="{{ json_encode($listadoInscriptos) }}"
            prop-mi-grupo = "{{ json_encode($miGrupo) }}"
            prop-grupos-subordinados="{{ json_encode($gruposSubordinados) }}"
            prop-evaluados="{{ json_encode($evaluados) }}"
    >
    </contenedor-evaluaciones>

    @if($actividad->linkEvaluacion)
        <div>
            <h4 class="subtitle">3 . {{ __('frontend.continue_with_feedback') }}</h4>
            <iframe src="{{ $actividad->linkEvaluacion }}/viewform?embedded=true" 
                width="100%" height="500" frameborder="0" marginheight="0" marginwidth="0">Cargando…</iframe>
        </div>
    @endif

    @if($miGrupo->linkEvaluacion != '')
        <div>
            <h4 class="subtitle">4 . {{ __('frontend.continue_with_group_feedback') }}</h4>
            <iframe src="{{ $miGrupo->linkEvaluacion }}/viewform?embedded=true" 
                width="100%" height="1048" frameborder="0" marginheight="0" marginwidth="0">Cargando…</iframe>
        </div>
    @endif

@endsection

@section('additional_scripts')

@endsection

@section('footer')
    @include('partials.footer')
@endsection
