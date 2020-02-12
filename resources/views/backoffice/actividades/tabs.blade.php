<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}" aria-expanded="true">General</a>
    </li>
    <li class="{{ ($tab == 'puntos')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/puntos" aria-expanded="true">Puntos de encuentro</a>
    </li>
    <li class="{{ ($tab == 'inscripciones')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/inscripciones" aria-expanded="true">Inscripciones</a>
    </li>
    <li class="{{ ($tab == 'grupos')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/grupos" aria-expanded="true">Grupos</a>
    </li>
    <li class="{{ ($tab == 'evaluaciones')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/evaluaciones" aria-expanded="true">Evaluaciones</a>
    </li>
    <li class="{{ ($tab == 'accesos')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/accesos" aria-expanded="true">Coordinadores</a>
    </li>
</ul>