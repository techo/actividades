<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/configuracion/provincias/{{ $idProvincia }}" aria-expanded="true">General</a>
    </li>
    <li class="{{ ($tab == 'localidades')?'active':'' }}">
        <a href="/admin/configuracion/provincias/{{ $idProvincia }}/localidades/" aria-expanded="true">Divisiones</a>
    </li>
</ul>