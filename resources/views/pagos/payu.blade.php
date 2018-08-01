{{--http://developers.payulatam.com/es/web_checkout/integration.html--}}
{{  }}
<form method="post" action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/">
    <input name="merchantId"    type="hidden"  value="508029"   >
    <input name="referenceCode" type="hidden"  value="{{ $referenceCode}}" >
    <input name="description"   type="hidden"  value="{{ $actividad->nombreActividad }}"  >
    <input name="amount"        type="hidden"  value="{{ $actividad->costo }}"   >
    <input name="tax"           type="hidden"  value="0"  >
    <input name="taxReturnBase" type="hidden"  value="0" >
    <input name="signature"     type="hidden"  value="{{ $signature }}"  >
    <input name="accountId"     type="hidden"  value="512321" >
    <input name="currency"      type="hidden"  value="ARS" >
    <input name="buyerFullName"      type="hidden"  value="{{ auth()->user()->nombreCompleto }}" >
    <input name="buyerEmail"      type="hidden"  value="{{ auth()->user()->mail }}" >
    <input name="telephone"      type="hidden"  value="{{ auth()->user()->telefonoMovil }}" >
    <input name="test"          type="hidden"  value="1" >
    <input name="responseUrl"    type="hidden"  value="http://atlas.test/pagos/{{ $actividad->idActividad }}/response" >
    <input name="confirmationUrl"    type="hidden"  value="http://atlas.test/pagos/confirmation" >
    <button name="btnPago"        type="submit"  class="btn btn-primary">
        <i class="fas fa-external-link-alt"></i> Confirmar con tu donación
    </button>
</form>