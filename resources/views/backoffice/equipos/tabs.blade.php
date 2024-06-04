<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}" aria-expanded="true">{{ __('backend.general') }}</a>
    </li>
    <li class="{{ ($tab == 'integrantes')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}/integrantes/" aria-expanded="true">{{ __('backend.members') }}</a>
    </li>
    <li class="{{ ($tab == 'coordinacion')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}/coordinacion/" aria-expanded="true">{{ __('backend.coordination') }}</a>
    </li>
</ul>