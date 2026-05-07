@extends('main')

@section('page_title')
    {{ __('frontend.last_step_confirm_by_donation') }}
@endsection

@section('main_image')
    <div class="techo-hero actividades"><h2></h2></div>
@endsection

@section('main_content')
@php
    $stripeConfig     = json_decode($actividad->pais->config_pago);
    $stripeHabilitado = !empty($stripeConfig->stripe_secret) && $inscripcion->pago != 1;
    $tieneLink        = !empty($actividad->linkPago);

    // Tab seleccionado por defecto: card si hay Stripe, transfer en caso contrario
    $tabDefault = $stripeHabilitado ? 'card' : 'transfer';
@endphp

<div class="container py-4">

    {{-- ── Step indicator ─────────────────────────────────────── --}}
    @include('partials.inscripcion-breadcrumb', ['flowSteps' => $flowSteps ?? []])

    {{-- ── Activity header ─────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body pb-3">
            <p class="text-uppercase text-muted small font-weight-bold mb-1">
                {{ __('frontend.inscription_for_activity') }}
            </p>
            <h4 class="font-weight-bold mb-3">
                <a href="/actividades/{{ $actividad->idActividad }}" class="text-dark">
                    {{ $actividad->nombreActividad }}
                </a>
            </h4>
            <div class="d-flex flex-wrap align-items-center text-muted" style="gap:1.2rem; font-size:.875rem;">
                @if($actividad->fechaInicio)
                    <span>
                        <i class="far fa-calendar-alt mr-1"></i>
                        {{ $actividad->fechaInicio->format('d.m') }} – {{ $actividad->fechaFin->format('d.m.Y') }}
                    </span>
                    <span>
                        <i class="far fa-clock mr-1"></i>
                        {{ $actividad->fechaInicio->format('H:i') }}
                    </span>
                @endif
                <span>
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    {{ strtoupper($actividad->pais->nombre) }}
                </span>
            </div>
        </div>
    </div>

    @if($actividad->pago == 1)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            {{-- ── Intro ──────────────────────────────────────────── --}}
            <p class="font-weight-bold mb-1">{{ __('frontend.payment_required_message') }}</p>
            <p class="text-muted mb-4" style="font-size:.9rem;">
                {{ __('frontend.payment_select_method') }}
            </p>

            {{-- ── Method selector ────────────────────────────────── --}}
            <div class="row mb-4" id="pago-metodo-selector">

                @if($stripeHabilitado)
                <div class="col-md-4 mb-2">
                    <button type="button"
                        class="pago-metodo-btn w-100 p-3 border rounded d-flex flex-column align-items-center justify-content-center"
                        data-metodo="card"
                        onclick="pagoSelectMetodo('card')">
                        <i class="far fa-credit-card fa-2x mb-2"></i>
                        <span class="font-weight-bold" style="font-size:.875rem;">
                            {{ __('frontend.payment_method_card') }}
                        </span>
                    </button>
                </div>
                @endif

                <div class="col-md-4 mb-2">
                    <button type="button"
                        class="pago-metodo-btn w-100 p-3 border rounded d-flex flex-column align-items-center justify-content-center"
                        data-metodo="transfer"
                        onclick="pagoSelectMetodo('transfer')">
                        <i class="fas fa-university fa-2x mb-2"></i>
                        <span class="font-weight-bold" style="font-size:.875rem;">
                            {{ __('frontend.payment_method_transfer') }}
                        </span>
                    </button>
                </div>

                {{-- TODO: link de pago — se implementa más adelante
                @if($tieneLink)
                <div class="col-md-4 mb-2">
                    <a href="{{ $actividad->linkPago }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="pago-metodo-btn w-100 p-3 border rounded d-flex flex-column align-items-center justify-content-center text-decoration-none"
                       style="color:inherit;">
                        <i class="fas fa-external-link-alt fa-2x mb-2"></i>
                        <span class="font-weight-bold" style="font-size:.875rem;">
                            {{ __('frontend.payment_method_link') }}
                        </span>
                    </a>
                </div>
                @endif
                --}}

            </div>

            {{-- ── Panel: Tarjeta / Stripe ─────────────────────────── --}}
            @if($stripeHabilitado)
            <div id="pago-content-card" class="pago-panel" style="display:none;">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="text-center py-4">
                    <p class="text-muted mb-4">{{ __('frontend.stripe_redirect_note') }}</p>
                    <form action="{{ route('stripe.checkout', ['idInscripcion' => $inscripcion->idInscripcion]) }}"
                          method="POST">
                        @csrf
                        <button type="submit" class="btn btn-dark px-5">
                            <i class="far fa-credit-card mr-2"></i>
                            {{ __('frontend.pay_with_stripe') }}
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- ── Panel: Efectivo / Transferencia ─────────────────── --}}
            <div id="pago-content-transfer" class="pago-panel" style="display:none;">
                <div class="row">

                    {{-- Left column: bank details --}}
                    <div class="col-md-6 mb-4 mb-md-0">
                        <p class="font-weight-bold mb-3">{{ __('frontend.bank_details_title') }}</p>
                        @if($actividad->descripcionPago)
                            <div>{!! $actividad->descripcionPago !!}</div>
                        @endif
                    </div>

                    {{-- Right column: voucher upload --}}
                    <div class="col-md-6">
                        <p class="font-weight-bold mb-3">{{ __('frontend.upload_voucher_title') }}</p>
                        <confirmacion-pago
                            id="{{ $inscripcion->idInscripcion }}"
                            voucher="{{ $inscripcion->voucherUrl }}"
                            csrf_token="{{ csrf_token() }}">
                        </confirmacion-pago>
                        @if($inscripcion->voucherUrl)
                            <p class="text-success mt-2 small">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ __('frontend.payment_in_process') }}
                            </p>
                        @endif
                    </div>

                </div>
            </div>

            {{-- TODO: panel link de pago — se implementa más adelante
            @if($tieneLink)
            <div id="pago-content-link" class="pago-panel" style="display:none;">
                <div class="text-center py-4">
                    <p class="text-muted mb-4">{{ __('frontend.payment_link_instructions') }}</p>
                    <a href="{{ $actividad->linkPago }}"
                       class="btn btn-primary px-5"
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        {{ __('frontend.go_to_payment_link') }}
                    </a>
                </div>
            </div>
            @endif
            --}}

            {{-- ── Monto ────────────────────────────────────────────── --}}
            <div class="mt-4 pt-3 border-top">
                @if($actividad->montoMax === '0.00' || (float)$actividad->montoMax === 0.0)
                    <p class="font-weight-bold mb-0">
                        {{ __('frontend.valor_label') }} {{ $actividad->montoMin }} {{ $actividad->moneda }}
                    </p>
                @else
                    <p class="font-weight-bold mb-0">
                        {{ __('frontend.suggested_donation_between') }}
                        {{ $actividad->montoMin }} {{ __('frontend.and') }}
                        {{ $actividad->montoMax }} {{ $actividad->moneda }}
                    </p>
                @endif
            </div>

            {{-- ── Legacy: PayU / país específico (preservado) ────────── --}}
            @if($actividad->pais->id == 99999)
            <form action="{{ action('InscripcionesController@donacionCheckout', ['id' => $actividad->idActividad]) }}"
                  method="POST"
                  class="mt-3">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <label class="font-weight-bold small">{{ __('frontend.donation_ammount') }}</label>
                        <input type="number"
                               class="form-control"
                               placeholder="{{ $actividad->moneda }}"
                               name="monto"
                               min="1"
                               required
                               step="0.1">
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary mt-3">
                    {{ __('frontend.continue') }}
                </button>
            </form>
            @endif

        </div>

        {{-- ── Footer actions ──────────────────────────────────── --}}
        <div class="card-footer bg-white border-0 px-4 pb-4 d-flex justify-content-between align-items-center">
            <a href="{{ action('InscripcionesController@puntoDeEncuentro', ['id' => $actividad->idActividad]) }}"
               class="btn btn-link text-danger px-0">
                {{ __('frontend.cancel') }}
            </a>
            <div class="d-flex align-items-center">
                @if(Auth::check() && Auth::user()->estaPreInscripto($actividad->idActividad))
                    <a href="/" class="btn btn-outline-secondary mr-2">{{ __('frontend.go_back') }}</a>
                @else
                    <a href="{{ action('InscripcionesController@puntoDeEncuentro', ['id' => $actividad->idActividad]) }}"
                       class="btn btn-outline-secondary mr-2">
                        {{ __('frontend.go_back') }}
                    </a>
                @endif
                @if($inscripcion->voucherUrl || $inscripcion->pago == 1)
                    <a href="/" class="btn btn-primary">{{ __('frontend.continue') }}</a>
                @endif
            </div>
        </div>
    </div>

    @endif

    {{-- ── Beca ─────────────────────────────────────────────── --}}
    @if(!empty($actividad->beca))
    <div class="text-center mt-3">
        <small class="text-muted">
            {{ __('frontend.also_you_can') }}
            <a href="{{ $actividad->beca }}" target="_blank">{{ __('frontend.ask_for_grant') }}</a>
        </small>
    </div>
    @endif

</div>
@endsection

@push('additional_scripts')
<script>
(function () {
    var tabDefault = '{{ $tabDefault }}';

    window.pagoSelectMetodo = function (metodo) {
        // Actualizar estado visual de los botones
        document.querySelectorAll('.pago-metodo-btn').forEach(function (btn) {
            btn.classList.remove('pago-metodo-btn--activo');
        });
        var btn = document.querySelector('[data-metodo="' + metodo + '"]');
        if (btn) btn.classList.add('pago-metodo-btn--activo');

        // Mostrar el panel correcto
        document.querySelectorAll('.pago-panel').forEach(function (panel) {
            panel.style.display = 'none';
        });
        var panel = document.getElementById('pago-content-' + metodo);
        if (panel) panel.style.display = 'block';
    };

    document.addEventListener('DOMContentLoaded', function () {
        pagoSelectMetodo(tabDefault);
    });

    // Hero background
    var bg = document.getElementById('main-background');
    if (bg) {
        bg.style.backgroundImage = 'url(/img/background-perfil.png)';
        bg.style.backgroundSize  = 'cover';
    }
}());
</script>

<style>
    /* ── Method buttons ── */
    .pago-metodo-btn {
        background: #fff;
        cursor: pointer;
        color: #666;
        min-height: 90px;
        transition: border-color .15s, color .15s;
        border: 1.5px solid #dee2e6 !important;
        text-decoration: none;
    }
    .pago-metodo-btn:hover,
    .pago-metodo-btn--activo {
        border-color: #0092dd !important;
        color: #0092dd;
    }
    .pago-metodo-btn--activo {
        font-weight: 600;
    }
</style>
@endpush

@section('footer')
    @include('partials.footer')
@endsection
