<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}" aria-expanded="true">{{__('backend.general')}}</a>
    </li>
    <li class="{{ ($tab == 'puntos')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/puntos" aria-expanded="true">{{__('backend.meeting_points')}}</a>
    </li>
    <li class="{{ ($tab == 'inscripciones')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/inscripciones" aria-expanded="true">{{__('backend.registrations')}}</a>
    </li>
    <li class="{{ ($tab == 'grupos')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/grupos" aria-expanded="true">{{__('backend.groups')}}</a>
    </li>
    <li class="{{ ($tab == 'evaluaciones')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/evaluaciones" aria-expanded="true">{{__('backend.evaluations')}}</a>
    </li>
    <li class="{{ ($tab == 'accesos')?'active':'' }}">
        <a href="/admin/actividades/{{ $actividad->idActividad }}/accesos" aria-expanded="true">{{__('backend.coordinators')}}</a>
    </li>
</ul>