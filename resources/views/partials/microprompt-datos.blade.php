{{--
    Microprompt de calidad de datos (opt-in por CALIDAD_DATOS_MICROPROMPT).
    Muestra los datos identitarios de la persona para que confirme que están
    bien (para su seguro) o los corrija. "Están correctos" confirma por AJAX
    (setea datos_verificados_at) sin recargar, para no perder el estado del
    formulario de confirmación de inscripción. Recibe $calidad (evaluación de
    App\Services\CalidadDatos\CalidadDatosPersona).
--}}
@php
    $u = auth()->user();
    $campos = $calidad['campos'] ?? [];
    $okNombre = ($campos['nombre']['estado'] ?? 'ok') === 'ok'
             && ($campos['apellido']['estado'] ?? 'ok') === 'ok';
    $filas = [
        ['label' => __('frontend.vd_campo_nombre'),     'valor' => trim($u->nombres . ' ' . $u->apellidoPaterno), 'ok' => $okNombre],
        ['label' => __('frontend.vd_campo_documento'),  'valor' => $u->dni,             'ok' => ($campos['documento']['estado'] ?? 'ok') === 'ok'],
        ['label' => __('frontend.vd_campo_nacimiento'), 'valor' => $u->fechaNacimiento, 'ok' => ($campos['fecha_nacimiento']['estado'] ?? 'ok') === 'ok'],
    ];
@endphp

<div id="microprompt-datos" class="microprompt-datos alert alert-warning">
    <h5 class="mb-1"><i class="fas fa-id-card mr-2"></i>{{ __('frontend.vd_titulo') }}</h5>
    <p class="mb-2">{{ __('frontend.vd_texto') }}</p>
    <ul class="list-unstyled mb-3">
        @foreach($filas as $f)
            <li class="d-flex justify-content-between align-items-center py-1 microprompt-fila">
                <span class="text-muted mr-3">{{ $f['label'] }}</span>
                <span class="text-right">
                    @if($f['valor'])
                        {{ $f['valor'] }}
                    @else
                        <em class="text-muted">{{ __('frontend.vd_sin_cargar') }}</em>
                    @endif
                    @unless($f['ok'])
                        <i class="fas fa-exclamation-triangle text-warning ml-1" title="{{ __('frontend.vd_revisar_campo') }}"></i>
                    @endunless
                </span>
            </li>
        @endforeach
    </ul>
    <div id="microprompt-datos-acciones" class="d-flex align-items-center flex-wrap">
        <button type="button" id="btn-datos-ok" class="btn btn-primary btn-sm mr-2">{{ __('frontend.vd_estan_bien') }}</button>
        <a href="/perfil" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">{{ __('frontend.vd_corregir') }}</a>
    </div>
    <p id="microprompt-datos-gracias" class="text-success mb-0 mt-2" style="display:none;">
        <i class="fas fa-check mr-1"></i>{{ __('frontend.vd_gracias') }}
    </p>
</div>

@push('additional_scripts')
<script>
(function(){
    var btn = document.getElementById('btn-datos-ok');
    if(!btn) return;
    btn.addEventListener('click', function(){
        btn.disabled = true;
        fetch('/ajax/usuario/verificar-datos', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }).then(function(r){ return r.ok ? r.json() : Promise.reject(r); })
          .then(function(){
              var acciones = document.getElementById('microprompt-datos-acciones');
              if(acciones) acciones.style.display = 'none';
              var gracias = document.getElementById('microprompt-datos-gracias');
              if(gracias) gracias.style.display = 'block';
          })
          .catch(function(){ btn.disabled = false; });
    });
})();
</script>
@endpush

@push('additional_styles')
<style>
    .microprompt-datos { border-radius: 10px; }
    .microprompt-datos .microprompt-fila + .microprompt-fila { border-top: 1px solid rgba(0,0,0,.06); }
</style>
@endpush
