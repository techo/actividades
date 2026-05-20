# Push Notifications — Documentación y guía de pruebas

## Índice
- [Arquitectura](#arquitectura)
- [Notificaciones activas](#notificaciones-activas)
- [Traducciones](#traducciones)
- [Comandos cron](#comandos-cron)
- [Guía de pruebas desde el navegador](#guía-de-pruebas-desde-el-navegador)
- [Diagnóstico](#diagnóstico)
- [Notificaciones pendientes (próximas iteraciones)](#notificaciones-pendientes-próximas-iteraciones)

---

## Arquitectura

```
App/dispositivo
    └── OneSignalService          API directa a OneSignal (batching de player_ids)
    └── PushNotificationService   Capa de negocio — verifica opt-in, despacha job
    └── EnviarNotificacionPush    Job en cola — reintentos con backoff exponencial
```

El locale del usuario se resuelve desde `persona->pais->locale` (ej: `es_AR`, `es_CH`, `pt`, `en`).  
Los textos de cada notificación están en `resources/lang/{locale}/push.php`.

### Método principal
```php
$pushService->enviarLocalizado(
    $persona,               // modelo Persona
    'push.clave_titulo',    // clave en push.php
    'push.clave_cuerpo',    // clave en push.php
    ['actividad' => '...'], // parámetros de interpolación
    ['tipo' => '...']       // datos extra para la app (deep link, etc.)
);
```

---

## Notificaciones activas

| # | Nombre | Trigger | Tipo |
|---|--------|---------|------|
| 1 | [Inscripción Confirmada](#1-inscripción-confirmada) | Backend aprueba sin pago | Evento |
| 2 | [Aviso de Pago Pendiente](#2-aviso-de-pago-pendiente) | Admin confirma + pago requerido | Evento |
| 3 | [Confirmación de Pago Exitoso](#3-confirmación-de-pago-exitoso) | Admin valida el pago | Evento |
| 4 | [Cambio en la Actividad](#4-cambio-en-la-actividad) | Admin cambia punto de encuentro | Evento |
| 5 | [Recordatorio de Asistencia](#5-recordatorio-de-asistencia-24hs) | 24hs antes del inicio | Cron diario 08:00 |
| 6 | [Apertura de Evaluación](#6-apertura-del-período-de-evaluación) | `fechaInicioEvaluaciones` == hoy | Cron diario 09:00 |
| 7 | [Recordatorio de Evaluación](#7-recordatorio-de-evaluación) | `fechaFinEvaluaciones` == mañana | Cron diario 09:00 |
| 8 | [Recordatorio de Pago](#8-recordatorio-de-pago-48hs) | 48hs desde confirmación sin pago | Cron diario 10:00 |
| 9 | [Reactivación de Voluntarios](#9-reactivación-de-voluntarios) | 30 días sin inscripciones | Cron mensual día 1 |

---

## Traducciones

Cada notificación tiene título y cuerpo traducidos para los 5 locales del sistema:

| Archivo | Región |
|---------|--------|
| `resources/lang/es_AR/push.php` | Argentina (voseo) |
| `resources/lang/es_CH/push.php` | Chile y resto de Latinoamérica |
| `resources/lang/es/push.php` | Español genérico (fallback) |
| `resources/lang/pt/push.php` | Brasil / Portugal |
| `resources/lang/en/push.php` | Inglés |

---

## Comandos cron

| Comando | Frecuencia | Descripción |
|---------|-----------|-------------|
| `php artisan actividad:recordatorio` | Diario 08:00 | Recordatorio 24hs antes de actividad |
| `php artisan push:apertura-evaluacion` | Diario 09:00 | Inicio del período de evaluación |
| `php artisan push:recordatorio-evaluacion` | Diario 09:00 | Cierre del período de evaluación (mañana) |
| `php artisan push:recordatorio-pago` | Diario 10:00 | Pago pendiente desde hace ~48hs |
| `php artisan push:reactivacion-voluntarios` | Mensual día 1 10:00 | Voluntarios inactivos 30+ días |

---

## Guía de pruebas desde el navegador

### Antes de empezar
- Tené la app móvil abierta (o en segundo plano) en el dispositivo de prueba
- El usuario de prueba debe tener la app instalada con notificaciones activadas
- Abrí el backoffice en otra pestaña del navegador

---

### 1. Inscripción Confirmada
> 📲 *"¡Inscripción confirmada! 🎉"*

**Desde:** La app o el sitio web (flujo del voluntario)

1. Buscá una actividad con **Confirmación = No** y **Pago = No**
2. Completá el formulario de inscripción y confirmá

---

### 2. Aviso de Pago Pendiente
> 📲 *"¡Tu cupo está casi listo! ⏳"*

**Desde:** Backoffice → actividad con pago requerido

1. El voluntario se inscribe a una actividad con **Confirmación = Sí** y **Pago = Sí**
2. Backoffice → **Actividades → [la actividad] → Inscriptos**
3. Buscá al voluntario y activá el toggle de **Confirmar**

---

### 3. Confirmación de Pago Exitoso
> 📲 *"¡Pago recibido! ✅"*

**Desde:** Backoffice → misma actividad con pago

1. Continuando del caso anterior (inscripto confirmado, pago pendiente)
2. En **Inscriptos**, activá el toggle de **Pago** del mismo voluntario

---

### 4. Cambio en la Actividad
> 📲 *"⚠️ Cambio en tu actividad"*

**Desde:** Backoffice → sección de inscriptos

1. Backoffice → **Actividades → [la actividad] → Inscriptos**
2. Seleccioná uno o varios inscriptos con el checkbox
3. Usá la acción de **Cambiar punto de encuentro**

---

### 5. Recordatorio de Asistencia (24hs)
> 📲 *"¡Prepárate! Tu actividad es mañana 📅"*

**Desde:** Backoffice → edición de actividad + terminal

1. Backoffice → **Actividades → [la actividad] → Editar**
2. Cambiá la **Fecha de inicio** a mañana y guardá
3. Ejecutá el comando: `php artisan actividad:recordatorio`

> En producción este comando corre automáticamente a las 08:00.

---

### 6. Apertura del Período de Evaluación
> 📲 *"¡Queremos escucharte! 🗣️"*

**Desde:** Backoffice → edición de actividad + terminal

1. Backoffice → **Actividades → [la actividad] → Editar**
2. Ponés la **Fecha de inicio de evaluaciones** en la fecha de hoy y guardás
3. Ejecutá el comando: `php artisan push:apertura-evaluacion`

---

### 7. Recordatorio de Evaluación
> 📲 *"¡No te olvides de evaluar tu experiencia!"*

**Desde:** Backoffice → edición de actividad + terminal

1. Backoffice → **Actividades → [la actividad] → Editar**
2. Ponés la **Fecha de fin de evaluaciones** en mañana y guardás
3. Ejecutá el comando: `php artisan push:recordatorio-evaluacion`

---

### 8. Recordatorio de Pago (48hs)
> 📲 *"No pierdas tu lugar ⚠️"*

**Desde:** Backoffice + terminal

1. Confirmá la inscripción de un voluntario (caso 2) sin tocar el pago
2. Ejecutá el comando: `php artisan push:recordatorio-pago`

> El comando busca inscripciones confirmadas sin pago actualizadas entre 47 y 49 horas atrás. Para probar sin esperar, ajustá el `updated_at` de la inscripción en la base de datos.

---

### 9. Reactivación de Voluntarios
> 📲 *"¡Te extrañamos en el terreno! 👋"*

**Desde:** Terminal (no tiene trigger de UI)

1. Usá un usuario de prueba sin inscripciones en los últimos 30 días
2. Ejecutá el comando: `php artisan push:reactivacion-voluntarios`

---

## Diagnóstico

Si no llega ninguna push, verificar en este orden:

1. **¿Tiene dispositivo registrado?**  
   El usuario debe haber abierto la app al menos una vez después de aceptar las notificaciones. Ver tabla `Dispositivo` → columna `activo = 1`.

2. **¿Tiene push activadas?**  
   Ver campo `recibir_push = 1` en tabla `Persona`.

3. **¿Se despachó el job?**  
   Revisar los logs: `storage/logs/laravel.log` — buscar `EnviarNotificacionPush`.

4. **¿Falló el envío a OneSignal?**  
   Mismo log, buscar `OneSignalService`. Los `player_id` inválidos se loguean como warnings sin interrumpir el flujo.

5. **¿Está corriendo el worker de colas?**  
   Las notificaciones se envían de forma asíncrona. Si la cola no está procesando: `php artisan queue:work`.

---

## Notificaciones pendientes (próximas iteraciones)

Las siguientes notificaciones están definidas pero dependen de tags en `actividades_tags` que aún no están implementadas en el backoffice:

| Nombre | Trigger |
|--------|---------|
| Construcción / Evento Masivo | Actividad publicada con tag `masiva` |
| Hito Nacional / Institucional | Actividad publicada con tag `hito` |
| Actividades para Nuevos Voluntarios | Actividad con nivel `nuevos_voluntarios` + usuario con 0-1 participaciones |
| Captación para Equipo Permanente | Actividad con tag `jornada_insercion` + voluntario con 3+ actividades |
| Resumen de Nuevas Actividades | Acumulado semanal de actividades nuevas en la sede del usuario |
| Lista de Espera — Cupo liberado | Usuario en lista de espera + alguien se da de baja (feature no implementada) |
