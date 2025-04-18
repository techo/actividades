<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}" aria-expanded="true">{{ __('backend.general') }}</a>
    </li>
    <li class="{{ ($tab == 'actividades')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/actividades/" aria-expanded="true">{{ __('backend.activities') }}</a>
    </li>
    <li class="{{ ($tab == 'equipos')?'active':'' }}">
        <a href="/admin/comunidades/{{ $idComunidad }}/equipos/" aria-expanded="true">{{ __('backend.teams') }}</a>
    </li>
</ul>