<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}" aria-expanded="true">General</a>
    </li>
    <li class="{{ ($tab == 'integrantes')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}/integrantes/" aria-expanded="true">Integrantes</a>
    </li>
    <li class="{{ ($tab == 'coordinacion')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}/coordinacion/" aria-expanded="true">Coordinación</a>
    </li>
</ul>