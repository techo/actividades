<ul class="nav nav-tabs">
    <li class="{{ ($tab == 'general') ? 'active' : '' }}">
        <a href="/admin/campanas/{{ $campana->id }}" aria-expanded="true">{{ __('backend.general') }}</a>
    </li>
    <li class="{{ ($tab == 'preguntas') ? 'active' : '' }}">
        <a href="/admin/campanas/{{ $campana->id }}/preguntas" aria-expanded="true">{{ __('backend.campaign_questions') }}</a>
    </li>
    <li class="{{ ($tab == 'suscriptos') ? 'active' : '' }}">
        <a href="/admin/campanas/{{ $campana->id }}/suscriptos" aria-expanded="true">{{ __('backend.subscribed') }}</a>
    </li>
</ul>
