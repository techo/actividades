@extends('main')

@section('page_title')
    {{ __('frontend.last_step_confirm_by_donation') }}
@endsection

@section('main_image')
    <div class="techo-hero actividades"><h2></h2></div>
@endsection

@section('main_content')

@php
    $stripeConfig = json_decode($actividad->pais->config_pago);

    // Métodos que habilitó el coordinador (Actividad.metodos_pago, cast array).
    // Puede ser null en actividades previas al rediseño (migración 2026_07_02).
    $metodos      = $actividad->metodos_pago;
    $tieneMetodos = is_array($metodos);

    // Tarjeta (Stripe): ESTRICTO — solo si el coordinador habilitó 'tarjeta' y el país
    // tiene Stripe. metodos_pago null => tarjeta oculta (no basta con que Stripe exista).
    $tarjetaHabilitada = $tieneMetodos && !empty($metodos['tarjeta']);
    $stripeHabilitado  = !empty($stripeConfig->stripe_secret) && $inscripcion->pago != 1 && $tarjetaHabilitada;

    // Transferencia y link: se respeta metodos_pago cuando está definido; si es null
    // (legacy) se conserva el comportamiento previo para no dejar sin forma de pago a
    // las actividades viejas (transferencia siempre; link si hay linkPago).
    $transferenciaHabilitada = $tieneMetodos ? !empty($metodos['transferencia']) : true;
    $linkHabilitado          = $tieneMetodos ? !empty($metodos['link_pix'])      : true;
    $tieneLink               = !empty($actividad->linkPago) && $linkHabilitado;

    $algunMetodo = $stripeHabilitado || $transferenciaHabilitada || $tieneLink;

    // Tab por defecto: primer método disponible (card > link > transfer).
    $tabDefault = $stripeHabilitado ? 'card' : ($tieneLink ? 'link' : ($transferenciaHabilitada ? 'transfer' : ''));
@endphp

@php
    $voucherRechazado = $inscripcion->voucher_rechazado ?? false;
    $voucherPendiente = ($inscripcion->voucherUrl || $inscripcion->scholarship_requested) && !$inscripcion->pago && !$voucherRechazado;
@endphp

<div class="container py-4">

    {{-- El aviso de comprobante rechazado (rojo) y el de "en validación" (amarillo) se
         muestran más abajo, debajo del encabezado de la actividad, y son excluyentes. --}}

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

    {{-- ── Estado del pago (excluyentes): rechazado (rojo) | en validación (amarillo) ── --}}
    @php
        $pagoResuelto            = $inscripcion->pago || $inscripcion->exento_pago;
        $becaEnValidacion        = $inscripcion->scholarship_requested && !$pagoResuelto && !$voucherRechazado;
        $comprobanteEnValidacion = $inscripcion->voucherUrl && !$pagoResuelto && !$voucherRechazado;
        $enValidacion            = $becaEnValidacion || $comprobanteEnValidacion;
        $avisoEsBeca             = $becaEnValidacion; // si hay beca, prima el mensaje de beca
    @endphp

    {{-- Rechazado (rojo): solo si el comprobante fue rechazado --}}
    @if($voucherRechazado)
    <div id="rechazado-banner" class="alert alert-danger d-flex align-items-start mb-4" style="border-radius:10px;">
        <i class="fa fa-times-circle fa-2x mr-3 mt-1 text-danger flex-shrink-0"></i>
        <div>
            <strong>{{ __('frontend.voucher_rechazado_titulo') }}</strong>
            <p class="mb-0 mt-1" style="font-size:.9rem;">{{ __('frontend.voucher_rechazado_subtitulo') }}</p>
            @if($inscripcion->voucher_rechazo_motivo)
                <p class="mb-0 mt-2 font-weight-bold" style="font-size:.85rem;">
                    {{ __('frontend.voucher_rechazado_motivo') }}: {{ $inscripcion->voucher_rechazo_motivo }}
                </p>
            @endif
        </div>
    </div>
    @endif

    {{-- En validación (amarillo): comprobante/beca enviado y aún sin resolver. Siempre en el
         DOM; lo muestra el server al cargar, o el JS al enviar sin recargar. Excluyente con el rojo. --}}
    <div id="validacion-banner" class="d-flex align-items-start mb-4"
         style="border-radius:10px; background:#fff6e6; border:1px solid #ffe0a6; padding:16px 18px; {{ $enValidacion ? '' : 'display:none;' }}">
        <i class="far fa-clock fa-lg mr-3 mt-1 flex-shrink-0" style="color:#b56b00;"></i>
        <div>
            <strong id="validacion-banner-title" style="color:#b56b00;"
                    data-beca="{{ __('frontend.scholarship_pending_title') }}"
                    data-voucher="{{ __('frontend.voucher_pending_title') }}">
                {{ $avisoEsBeca ? __('frontend.scholarship_pending_title') : __('frontend.voucher_pending_title') }}
            </strong>
            <p id="validacion-banner-subtitle" class="mb-0 mt-1" style="font-size:.9rem; color:#2b2f36;"
               data-beca="{{ __('frontend.scholarship_pending_subtitle') }}"
               data-voucher="{{ __('frontend.voucher_pending_subtitle') }}">
                {{ $avisoEsBeca ? __('frontend.scholarship_pending_subtitle') : __('frontend.voucher_pending_subtitle') }}
            </p>
        </div>
    </div>

    {{-- ── Card de pago (siempre visible: no bloquea aunque haya comprobante/beca enviado) ── --}}
    <div id="payment-card" class="card border-0 shadow-sm">
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

                @if($transferenciaHabilitada)
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
                @endif

                @if($tieneLink)
                <div class="col-md-4 mb-2">
                    <button type="button"
                        class="pago-metodo-btn w-100 p-3 border rounded d-flex flex-column align-items-center justify-content-center"
                        data-metodo="link"
                        onclick="pagoSelectMetodo('link')">
                        <i class="fas fa-link fa-2x mb-2"></i>
                        <span class="font-weight-bold" style="font-size:.875rem;">
                            {{ __('frontend.payment_method_link') }}
                        </span>
                    </button>
                </div>
                @endif

            </div>

            @unless($algunMetodo)
            <div class="alert alert-warning" role="alert">
                {{ __('frontend.payment_no_method_available') }}
            </div>
            @endunless

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
            @if($transferenciaHabilitada)
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
                            voucher="{{ $voucherRechazado ? '' : $inscripcion->voucherUrl }}"
                            csrf_token="{{ csrf_token() }}">
                        </confirmacion-pago>
                        @if($inscripcion->voucherUrl && !$pagoResuelto && !$voucherRechazado)
                            <p class="text-success mt-2 small">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ __('frontend.payment_in_process') }}
                            </p>
                        @endif
                    </div>

                </div>
            </div>
            @endif

            {{-- ── Panel: Link de Pago ──────────────────────────────── --}}
            @if($tieneLink)
            <div id="pago-content-link" class="pago-panel" style="display:none;">
                <div class="row">

                    {{-- Left column: instructions + link button + scholarship --}}
                    <div class="col-md-6 mb-4 mb-md-0">
                        <p class="font-weight-bold mb-3">{{ __('frontend.payment_method_link') }}</p>
                        <p class="text-muted mb-4" style="font-size:.9rem;">
                            {{ __('frontend.payment_link_description') }}
                        </p>
                        <a href="{{ $actividad->linkPago }}"
                           class="btn btn-outline-primary mb-4"
                           target="_blank"
                           rel="noopener noreferrer">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            {{ strtoupper(__('frontend.go_to_payment_link')) }}
                        </a>
                    </div>

                    {{-- Right column: voucher upload (reutiliza componente y endpoint existentes) --}}
                    <div class="col-md-6">
                        <p class="font-weight-bold mb-3">{{ __('frontend.upload_voucher_title') }}</p>
                        <confirmacion-pago
                            id="{{ $inscripcion->idInscripcion }}"
                            voucher="{{ $voucherRechazado ? '' : $inscripcion->voucherUrl }}"
                            csrf_token="{{ csrf_token() }}">
                        </confirmacion-pago>
                        @if($inscripcion->voucherUrl && !$pagoResuelto && !$voucherRechazado)
                            <p class="text-success mt-2 small">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ __('frontend.payment_in_process') }}
                            </p>
                        @endif
                    </div>

                </div>
            </div>
            @endif

            {{-- ── Panel: Beca / Exención ───────────────────────────── --}}
            @if($actividad->permite_exencion)
            <div id="pago-content-beca" class="pago-panel" style="display:none;">
                <solicitud-beca
                    id="{{ $inscripcion->idInscripcion }}"
                    csrf_token="{{ csrf_token() }}">
                </solicitud-beca>
            </div>
            @endif

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
            @if($actividad->permite_exencion)
                <div class="mt-2">
                    <p class="text-muted small mb-2">
                        {{ __('frontend.payment_link_scholarship_note') }}
                    </p>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            onclick="mostrarBeca()">
                        {{ __('frontend.payment_link_scholarship_btn') }}
                    </button>
                </div>
            @endif
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
            <a href="/actividades/{{ $actividad->idActividad }}"
               class="btn btn-outline-secondary">
                {{ __('frontend.go_back') }}
            </a>
            <button id="btn-finalizar"
                    type="button"
                    class="btn btn-primary"
                    onclick="finalizarInscripcion()"
                    {{ ($inscripcion->voucherUrl || $inscripcion->scholarship_requested) && !$voucherRechazado ? '' : 'disabled' }}>
                {{ __('frontend.finish') }}
            </button>
        </div>
    </div>
        {{-- ── Beca (solo si no hay link de pago; si hay link, aparece dentro del panel) --}}
        @if(!empty($actividad->beca) && !$tieneLink)
        <div class="text-center mt-3">
            <small class="text-muted">
                {{ __('frontend.also_you_can') }}
                <a href="{{ $actividad->beca }}" target="_blank">{{ __('frontend.ask_for_grant') }}</a>
            </small>
        </div>
        @endif
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

    var _becaTabAnterior = tabDefault;

    window.mostrarBeca = function () {
        var activo = document.querySelector('.pago-metodo-btn--activo');
        _becaTabAnterior = activo ? activo.dataset.metodo : tabDefault;
        document.querySelectorAll('.pago-metodo-btn').forEach(function (b) {
            b.classList.remove('pago-metodo-btn--activo');
        });
        document.querySelectorAll('.pago-panel').forEach(function (p) {
            p.style.display = 'none';
        });
        var panel = document.getElementById('pago-content-beca');
        if (panel) panel.style.display = 'block';
    };

    window.becaGoBack = function () {
        var panel = document.getElementById('pago-content-beca');
        if (panel) panel.style.display = 'none';
        pagoSelectMetodo(_becaTabAnterior);
    };

    // Habilita el botón Finalizar y muestra el aviso "en validación" SIN ocultar la tarjeta de pago.
    // El usuario puede seguir pagando si quiere; el aviso solo informa el estado.
    window.notifyPagoListo = function () {
        var btn = document.getElementById('btn-finalizar');
        if (btn) btn.disabled = false;

        // Si acaba de re-subir tras un rechazo, ocultamos el aviso rojo (excluyente con el amarillo).
        var rechazado = document.getElementById('rechazado-banner');
        if (rechazado) rechazado.style.display = 'none';

        var banner = document.getElementById('validacion-banner');
        if (!banner) return;

        // ¿Se envió una beca o un comprobante? Lo deducimos del panel visible al confirmar.
        var becaPanel = document.getElementById('pago-content-beca');
        var esBeca = becaPanel && becaPanel.style.display !== 'none';

        var title = document.getElementById('validacion-banner-title');
        var subtitle = document.getElementById('validacion-banner-subtitle');
        if (title)    title.textContent    = title.getAttribute(esBeca ? 'data-beca' : 'data-voucher');
        if (subtitle) subtitle.textContent = subtitle.getAttribute(esBeca ? 'data-beca' : 'data-voucher');

        banner.style.display = '';
        banner.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // "Finalizar": NO redirige al home. Confirma en la misma página mostrando el aviso
    // "en validación" y llevando la vista hacia arriba. El usuario navega cuando quiera.
    window.finalizarInscripcion = function () {
        if (typeof window.notifyPagoListo === 'function') window.notifyPagoListo();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    document.addEventListener('DOMContentLoaded', function () {
        pagoSelectMetodo(tabDefault);
        // Deep-link desde el mail: /confirmar/donacion?opcion=beca abre directo la solicitud de beca
        var _params = new URLSearchParams(window.location.search);
        if (_params.get('opcion') === 'beca' && document.getElementById('pago-content-beca')) {
            mostrarBeca();
        }
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
