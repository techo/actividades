{{--http://developers.payulatam.com/es/web_checkout/integration.html--}}
@php
    $config = $payment->config();

@endphp
<form method="{{ $payment->method() }}" action="{{ $payment->url() }}">
    <input name="merchantId"    type="hidden"  value="{{ $config->merchant_id }}"   >
    <input name="referenceCode" type="hidden"  value="{{ $payment->referenceCode()}}" >
    <input name="description"   type="hidden"  value="{{ $actividad->nombreActividad . ', ' . $actividad->localidad->localidad . ', ' . $actividad->provincia->provincia}}"  >
    <input name="amount"        type="hidden"  value="{{ $payment->monto }}"   >
    <input name="tax"           type="hidden"  value="0">
    <input name="taxReturnBase" type="hidden"  value="0">
    <input name="signature"     type="hidden"  value="{{ $payment->signature() }}"  >
    <input name="accountId"     type="hidden"  value="{{ $config->account_id }}" >
    <input name="currency"      type="hidden"  value="{{ $actividad->moneda }}" >
    <input name="buyerFullName" type="hidden"  value="{{ auth()->user()->nombreCompleto }}" >
    <input name="buyerEmail"    type="hidden"  value="{{ auth()->user()->mail }}" >
    <input name="telephone"     type="hidden"  value="{{ auth()->user()->telefonoMovil }}" >
    <input name="test"          type="hidden"  value="1" >
    <input name="responseUrl"   type="hidden"  value="{{ url('/') }}/pagos/{{ $payment->inscripcion->idInscripcion }}/response" >
    <input name="confirmationUrl"    type="hidden"  value="{{ url('/') }}/pagos/{{ $payment->inscripcion->idInscripcion }}/confirmation" >

    <button name="btnPago"        type="submit"  class="btn btn-primary">
        <i class="fas fa-external-link-alt"></i> Confirmar con tu donación
    </button>
</form>