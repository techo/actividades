<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}" aria-expanded="true">{{ __('backend.general') }}</a>
    </li>
    <li class="{{ ($tab == 'ficha')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/ficha/" aria-expanded="true">{{ __('comunidad_ficha_inicial.ficha') }}</a>
    </li>
    <li class="{{ ($tab == 'ficha')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/coordinacion/" aria-expanded="true">{{ __('backend.coordination') }}</a>
    </li>
    <li class="{{ ($tab == 'actividades')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/actividades/" aria-expanded="true">{{ __('backend.activities') }}</a>
    </li>
    <li class="{{ ($tab == 'integrantes')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/integrantes/" aria-expanded="true">{{ __('backend.members') }}</a>
    </li>
</ul>