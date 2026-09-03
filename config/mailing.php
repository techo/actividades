<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Throttle de envíos MASIVOS (hub de comunicaciones)
    |--------------------------------------------------------------------------
    |
    | Reparte en el tiempo los mails del hub (invitaciones a actividad y
    | comunicaciones de campaña) para NO superar el límite del proveedor ni
    | ahogar la cola de transaccionales. NO afecta al transaccional (rápido)
    | ni al push. Ver App\Services\MailThrottle.
    |
    | Referencia de topes por proveedor (destinatarios/día):
    |   - Gmail smtp.gmail.com (usuario)   → ~2.000/día TOTAL  → hub ~1.500 (deja margen al transaccional)
    |   - Gmail smtp-relay.gmail.com       → ~10.000/día       → hub ~9.000
    |   - Amazon SES (post warm-up)        → sin tope real     → subir fuerte / desactivar
    |
    */

    // Tope de mails masivos por día. Debe quedar por DEBAJO del límite del proveedor,
    // dejando lugar para el transaccional (confirmaciones, recordatorios), que también
    // consume el cupo del proveedor pero NO se throttlea acá.
    'hub_por_dia' => (int) env('MAIL_HUB_POR_DIA', 1500),

    // Ritmo máximo por minuto (evita ráfagas que Gmail corta reseteando la conexión).
    // El espaciado real es el MAYOR entre este y el que impone el tope diario, así el
    // envío se reparte parejo a lo largo del día en vez de concentrarse.
    'hub_por_minuto' => (int) env('MAIL_HUB_POR_MINUTO', 60),

    // Tope DURO de destinatarios (email) por envío único. 0 = sin tope (confía en el
    // throttle). Sirve de freno de mano para que un "todos" gigante no se dispare por
    // accidente; si se supera, el envío se rechaza con 422 antes de encolar nada.
    'hub_max_por_envio' => (int) env('MAIL_HUB_MAX_POR_ENVIO', 0),

    /*
    |--------------------------------------------------------------------------
    | Red de seguridad: redirección de TODO el mail saliente (sandbox)
    |--------------------------------------------------------------------------
    |
    | Si está seteada, TODO mail saliente (masivo y transaccional) se redirige a
    | esta casilla en vez de al destinatario real — que queda marcado en el asunto.
    | Pensada para SANDBOX (cuya base tiene emails reales de voluntarios): permite
    | probar envíos masivos con mails reales sin escribirle a nadie real.
    | En PRODUCCIÓN debe quedar VACÍA. Ver App\Listeners\RedirigirMailSandbox.
    |
    */
    'redirect_to' => env('MAIL_REDIRECT_TO'),

];
