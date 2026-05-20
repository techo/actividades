{{--
  Dynamic enrollment breadcrumb partial.

  Required variables:
    $flowSteps  — array from InscripcionFlow::stepsWithState()
                  Each item: ['key', 'label_key', 'phase', 'state']
                  state → 'done' | 'active' | 'pending'

  Usage:
    @include('partials.inscripcion-breadcrumb', ['flowSteps' => $flowSteps])
--}}
@if (!empty($flowSteps))

{{-- CSS lives in <head> (outside #app) so Vue's compiler never sees <style> tags --}}
@push('additional_styles')
<style>
/* ── Inscription breadcrumb (Blade phase) ────────────────────────── */
.inscripcion-breadcrumb-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.inscripcion-breadcrumb-wrap::-webkit-scrollbar { height: 0; width: 0; }

.inscripcion-steps {
    flex-wrap: nowrap;
    padding: 0 8px 4px;
    gap: .4rem;
    min-width: max-content;
}

.inscripcion-step {
    font-size: .78rem;
    color: #bbb !important;
    list-style: none;
    white-space: nowrap;
}
.inscripcion-step--done {
    color: #aaa !important;
}
.inscripcion-step--active {
    color: #0092dd !important;
    font-weight: 700 !important;
    border-bottom: 2px solid #0092dd;
    padding-bottom: 1px;
}
.inscripcion-step-sep {
    font-size: .78rem;
    color: #ddd;
    list-style: none;
    padding: 0 .1rem;
    flex-shrink: 0;
}
</style>
@endpush

<nav class="inscripcion-breadcrumb-wrap d-flex justify-content-center mb-4"
     aria-label="{{ __('frontend.step_progress') }}">
    <ol class="inscripcion-steps list-unstyled d-flex align-items-center mb-0">
        @foreach ($flowSteps as $step)
            @if (!$loop->first)
                <li class="inscripcion-step-sep" aria-hidden="true">›</li>
            @endif
            <li class="inscripcion-step inscripcion-step--{{ $step['state'] }}"
                @if ($step['state'] === 'active') aria-current="step" @endif>
                {{ __('frontend.' . $step['label_key']) }}
            </li>
        @endforeach
    </ol>
</nav>
@endif
