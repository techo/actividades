@php
    $clases = ['nuevo' => 'label-default', 'triage' => 'label-info', 'en_progreso' => 'label-warning', 'resuelto' => 'label-success', 'descartado' => 'label-default'];
@endphp
<span class="label {{ $clases[$estado] ?? 'label-default' }}">{{ __('backend.my_reports_status.' . $estado) }}</span>
