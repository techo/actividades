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

    // Pausa de emergencia de TODO el envío masivo (recordatorios, invitaciones de
    // actividad, comunicaciones de campaña, invitaciones a evaluación). Cuando está
    // en true, esos envíos se SALTEAN (no se encolan) — el transaccional sigue normal.
    // Sirve para cortar un runaway o proteger el cupo del proveedor. Ver los guards
    // en los comandos/jobs de bulk.
    'bulk_pausado' => filter_var(env('MAIL_BULK_PAUSADO', false), FILTER_VALIDATE_BOOLEAN),

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

    // Ritmo (mails/min) para ESCALONAR batches transaccionales que se disparan de golpe
    // —hoy el cron de recordatorios (08:00), que encola un job por inscripción de todas
    // las actividades de mañana—. Sin escalonar, la ráfaga satura el relay (Google corta
    // la conexión → "empty response"). No es un tope diario: solo evita la ráfaga.
    'batch_por_minuto' => (int) env('MAIL_BATCH_POR_MINUTO', 120),

    /*
    |--------------------------------------------------------------------------
    | Dedup mail/push (no duplicar el mismo aviso en dos canales)
    |--------------------------------------------------------------------------
    |
    | Cuando un mismo evento manda push Y mail, si a la persona le llega el push
    | de forma confiable (app instalada + push activado + acceso reciente) NO le
    | mandamos también el mail. Baja el volumen de envíos y evita el doble aviso.
    | La "recencia" evita suprimir el mail de un device 'activo' pero muerto
    | (desinstalado sin logout). Ver App\Persona::tienePushConfiable().
    | Más chico = más conservador (suprime menos, manda más mail).
    |
    */

    // Recordatorio de actividad (cron 08:00): es el batch más grande, ventana amplia.
    'dedup_recencia_dias' => (int) env('MAIL_DEDUP_RECENCIA_DIAS', 60),

    // Avisos críticos de inscripción (confirmación, falta de pago): más importantes,
    // ventana más corta → solo se suprime el mail a usuarios de app muy activos.
    'dedup_recencia_dias_critico' => (int) env('MAIL_DEDUP_RECENCIA_DIAS_CRITICO', 30),

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
