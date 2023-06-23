<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}" aria-expanded="true">General</a>
    </li>
    <li class="{{ ($tab == 'personas')?'active':'' }}">
        <a href="/admin/equipos/{{ $idEquipo }}/personas/" aria-expanded="true">Personas</a>
    </li>
</ul>