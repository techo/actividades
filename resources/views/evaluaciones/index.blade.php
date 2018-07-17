@extends('main')

@section('page_title')
    Evaluaciones de {{ $actividad->nombreActividad }}
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <h2 class="text-uppercase">Si te da lo mismo, estás haciendo mal las cuentas <br>
            Anotate y participá</h2>
    </div>
@endsection

@section('main_content')
    <h1>Evaluaciones de {{ $actividad->nombreActividad }}</h1>
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
@endsection

@section('additional_scripts')

@endsection

@section('footer')
    @include('partials.footer')
@endsection
