<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}" aria-expanded="true">{{ __('backend.general') }}</a>
    </li>
    <li class="{{ ($tab == 'actividades')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/actividades/" aria-expanded="true">{{ __('backend.activities') }}</a>
    </li>
    <li class="{{ ($tab == 'integrantes')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/integrantes/" aria-expanded="true">{{ __('backend.members') }}</a>
    </li>
</ul>