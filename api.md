# API Reference — MiTECHO

Base URL: `https://{dominio}/api`

Autenticación: **Bearer Token** en el header `Authorization` para todas las rutas privadas.

```
Authorization: Bearer {token}
```

---

## Índice

- [Autenticación](#autenticación)
- [Usuario / Perfil](#usuario--perfil)
- [Actividades](#actividades)
- [Inscripciones](#inscripciones)
- [Evaluaciones](#evaluaciones)
- [Perfil médico y estudios](#perfil-médico-y-estudios)
- [Campañas](#campañas)
- [Dispositivos (Push)](#dispositivos-push)
- [Datos maestros](#datos-maestros)
- [Traducciones](#traducciones)

---

## Autenticación

### `POST /login`
Login con email y password.

**Body**
```json
{ "mail": "usuario@techo.org", "password": "****" }
```
**Response `200`**
```json
{ "access_token": "...", "token_type": "Bearer" }
```

---

### `POST /socialLogin`
Login con token de red social (Google / Facebook).

**Body**
```json
{ "provider": "google", "token": "..." }
```

---

### `POST /providerLogin`
Login con proveedor externo (Apple, etc).

**Body**
```json
{ "provider": "apple", "token": "..." }
```

**Errores posibles**
| Código | Motivo |
|--------|--------|
| `400` | Proveedor inválido |
| `401` | Token inválido |
| `403` | Email no verificado |
| `404` | Usuario no encontrado |
| `409` | Cuenta ya asociada a otro proveedor |

---

### `POST /register`
Registro de nuevo voluntario.

**Body** — ver validaciones en `CrearPersona` request.

---

### `POST /create`
Creación de usuario alternativa (sin validaciones estrictas de registro).

---

### `POST /resetPassword`
Envía email de recuperación de contraseña.

**Body**
```json
{ "email": "usuario@techo.org" }
```

---

### `POST /logout` 🔒
Invalida el token actual.

**Response `200`**
```json
{ "message": "Successfully logged out" }
```

---

### `POST /password/reset` 🔒
Reset de contraseña para usuario ya autenticado.

---

## Usuario / Perfil

### `GET /user` 🔒
Retorna los datos del usuario autenticado.

**Response `200`** — objeto `Persona` completo.

---

### `POST /ping` 🔒
Registra actividad reciente de la app. Actualiza `ultimo_acceso_app` con el timestamp actual.

> Útil para trackear qué usuarios siguen activos en la app. Llamar periódicamente mientras la app está en uso.

**Response `200`**
```json
{ "success": true }
```

---

### `GET /personas/{id}` 🔒
Retorna los datos de una persona por su ID.

---

### `POST /editPersona/{id}` 🔒
Actualiza los datos de una persona.

**Body** — campos editables del perfil (nombres, apellido, teléfono, etc).

---

### `POST /perfil/cambiar_photo` 🔒
Sube y actualiza la foto de perfil.

**Body** — `multipart/form-data` con campo `photo`.

---

### `DELETE /usuario` 🔒
Anonimiza la cuenta del usuario (soft delete de datos personales).

**Response `204`** — sin cuerpo.

---

## Actividades

### `GET /actividadesGeneral`
Lista de actividades públicas (sin autenticación).

**Query params** — ver filtros disponibles en `ActividadesSearch`.

---

### `GET /actividades` 🔒
Lista de actividades para usuario autenticado.

**Query params** — mismos filtros que `/actividadesGeneral`.

---

### `GET /actividades/{id}` 🔒
Detalle de una actividad.

---

### `GET /actividades/categoria/{nombre}` 🔒
Actividades filtradas por categoría.

**Valores válidos de `{nombre}`**

| Valor | Tipos de actividad incluidos |
|-------|------------------------------|
| `construcciones` | 11, 27, 65, 72, 73, 80, 81, 98, 105, 114, 115 |
| `mesas` | 25, 28, 29, 75, 76, 82, 83, 85, 113, 117 |
| `infraestructura` | 22, 32, 77, 79, 97, 103 |
| `formativos` | 23, 30, 31, 33–36, 45–47, 49, 52, 53, 56, 58, 59, 62, 89 |
| `encuentros` | 54, 55, 63, 64, 68, 69, 71, 86, 88, 90 |
| `colecta` | 43, 96 |
| `eventos` | 44, 48, 60, 101, 104 |

**Response `404`** si la categoría no existe.

---

## Inscripciones

### `GET /inscripciones` 🔒
Lista de inscripciones del usuario autenticado.

---

### `POST /inscripciones/actividad/{id}` 🔒
Inscribirse a una actividad.

**Body**
```json
{
  "punto_encuentro": 1,
  "aceptar_terminos": 1,
  "jornadas": "[{\"idJornada\": 1, \"selected\": true}]",
  "roles_aplicados": "[{\"id\": 2}]",
  "inscripciones_aplicadas": "[{\"id\": 3}]",
  "respuestas": "[{\"pregunta_id\": 1, \"respuesta\": \"Sí\"}]"
}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Inscripción confirmada",
  "actividad_id": 42,
  "inscripcion_id": 99,
  "estado_inscripcion": "CONFIRMADO"
}
```

**Estados posibles de `estado_inscripcion`**

| Estado | Descripción |
|--------|-------------|
| `CONFIRMADO` | Inscripción confirmada — se envía push + email |
| `PRE_INSCRIPTO` | Espera confirmación manual — se envía push + email |
| `FALTA_PAGO` | Requiere pago — se envía push + email |
| `FALTA_ACEPTAR_TERMINOS` | No se enviaron términos aceptados |
| `PUNTO_ENCUENTRO_CERRADO` | El punto de encuentro está cerrado |

---

### `DELETE /inscripciones/{id}` 🔒
Desinscribirse de una actividad.

---

### `POST /inscripciones/voucher` 🔒
Subir comprobante de pago para una inscripción.

**Body** — `multipart/form-data`
```
idInscripcion: 99
voucher: {archivo}
```

---

## Evaluaciones

### `GET /actividades/{id}/evaluaciones` 🔒
Lista de evaluaciones de una actividad.

---

### `GET /actividades/{id}/evaluaciones/tags` 🔒
Tags disponibles para evaluar una actividad.

---

### `POST /actividades/{id}/evaluaciones` 🔒
Evaluar una actividad.

---

### `POST /actividades/{id}/evaluaciones/persona/{idPersona}` 🔒
Evaluar a una persona dentro de una actividad.

---

### `POST /actividades/{id}/evaluaciones/impacto` 🔒
Registrar evaluación de impacto.

---

## Perfil médico y estudios

### `GET /perfil/fichaMedica` 🔒
Obtener ficha médica del usuario.

### `POST /perfil/fichaMedica` 🔒
Crear o actualizar ficha médica.

### `POST /perfil/fichaMedica/archivo_medico` 🔒
Subir archivo médico.

---

### `GET /perfil/estudios` 🔒
Listar estudios del usuario.

### `POST /perfil/estudios` 🔒
Agregar un estudio.

### `PUT /perfil/estudios` 🔒
Actualizar un estudio.

### `DELETE /perfil/estudios/{id}` 🔒
Eliminar un estudio.

---

### `GET /perfil/estudios/institucionEducativa` 🔒
Listar instituciones educativas.

### `GET /perfil/estudios/institucionEducativa/{id}` 🔒
Obtener una institución educativa por ID.

### `GET /perfil/estudios/institucionEducativa/pais/{idPais}` 🔒
Instituciones educativas filtradas por país.

---

## Campañas

Permite listar campañas activas (colectas, captaciones) y suscribir usuarios a ellas.

### `GET /campanas`
Lista de campañas activas. Paginado.

**Query params**

| Param | Tipo | Descripción |
|-------|------|-------------|
| `pais_id` | int | Filtra por país (usar el `id` del endpoint `/paises`) |
| `tipo` | string | `colecta` o `captacion` |
| `per_page` | int | Resultados por página (default `20`, máx `50`) |

**Response `200`**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "nombre": "Colecta Anual 2025",
      "descripcion": "...",
      "tipo": "colecta",
      "imagen": "/storage/campanas/abc.jpg",
      "whatsapp_link": "https://chat.whatsapp.com/...",
      "confirmation_message": "<p>¡Gracias por sumarte!</p>",
      "fecha_inicio": "2025-06-01",
      "fecha_fin": "2025-06-30",
      "activa": true,
      "preguntas": [
        {
          "id": 1,
          "pregunta": "¿Podés colaborar los fines de semana?",
          "tipo": "desplegable",
          "opciones": ["Sí", "No", "A veces"],
          "requerida": true,
          "orden": 1
        }
      ]
    }
  ],
  "per_page": 20,
  "total": 5
}
```

---

### `GET /campanas/{id}`
Detalle de una campaña activa. Incluye preguntas dinámicas y el `confirmation_message` (HTML) para mostrarlo post-suscripción.

**Response `200`** — mismo objeto que en el listado, con todos los campos.

**Response `404`** si la campaña no existe o está inactiva.

---

### `POST /campanas/{id}/suscribir` 🔒
Suscribe al usuario autenticado a la campaña.

> La app **no necesita enviar datos personales** (nombre, email, teléfono). El servidor los toma automáticamente del token Passport. Solo se envían las respuestas a preguntas dinámicas si las hay.

**Body** (todo opcional)
```json
{
  "respuestas": [
    { "pregunta_id": 1, "respuesta": "Sí" },
    { "pregunta_id": 2, "respuesta": "Los sábados" }
  ]
}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Gracias por inscribirte.",
  "whatsapp_link": "https://chat.whatsapp.com/...",
  "confirmation_message": "<p>¡Te esperamos!</p>"
}
```
> `whatsapp_link` y `confirmation_message` pueden ser `null` si la campaña no los tiene configurados. Mostrar solo si no son `null`.

**Response `422`** — usuario ya inscripto en esta campaña:
```json
{
  "already_registered": true,
  "message": "Ya estás inscripto/a en esta campaña."
}
```

**Response `404`** si la campaña no existe o está inactiva.

---

### `GET /campanas/{id}/suscripcion` 🔒
Verifica si el usuario autenticado ya está suscripto a la campaña. Útil para mostrar/ocultar el botón de inscripción.

**Response `200`**
```json
{ "inscripto": true }
```

---

## Dispositivos (Push)

Endpoints para integrar notificaciones push via **OneSignal**.
El `player_id` es el identificador que genera el SDK de OneSignal en el dispositivo.

### `POST /dispositivos` 🔒
Registra o actualiza el dispositivo del usuario.

> Llamar después del login y cuando el SDK de OneSignal entregue un nuevo `player_id`.

**Body**
```json
{
  "player_id": "a3f2b1c8-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
  "plataforma": "android"
}
```
`plataforma` acepta: `ios` | `android` | `null`

**Response `200`**
```json
{
  "success": true,
  "dispositivo": {
    "id": 1,
    "player_id": "a3f2b1c8-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "plataforma": "android"
  }
}
```

> Si el `player_id` ya existe (ej: cambio de teléfono), se reasigna al usuario actual y se reactiva automáticamente.

---

### `DELETE /dispositivos/{player_id}` 🔒
Desactiva el dispositivo al hacer logout.

> No elimina el registro, solo lo marca como inactivo para conservar historial.

**Response `200`**
```json
{ "success": true }
```

**Response `404`** si el `player_id` no pertenece al usuario autenticado.

---

## Datos maestros

### `GET /categorias`
Lista de categorías de actividades.

### `GET /sedes`
Lista de sedes/oficinas.

### `GET /paises`
Lista de países.

### `GET /paises/habilitados`
Lista de países habilitados en el sistema.

### `GET /paises/{id_pais}/provincias`
Provincias de un país.

### `GET /paises/{id_pais}/provincias/{id_provincia}/localidades`
Localidades de una provincia.

---

## Traducciones

### `GET /translate`
Obtener una traducción.

### `POST /translate/batch`
Obtener múltiples traducciones en una sola llamada.

---

## Deep Linking

La app soporta los siguientes mecanismos para abrir pantallas directamente:

### Custom URL Scheme
```
mitecho://actividades/{id}     → abre una actividad específica
mitecho://                     → abre la app en la pantalla principal
```

### Universal Links (iOS) / App Links (Android)
Los archivos de configuración están hosteados en:
```
https://actividades.techo.org/.well-known/apple-app-site-association
https://actividades.techo.org/.well-known/assetlinks.json
```
Cuando el usuario toca un link `https://actividades.techo.org/actividades/{id}` en su celular y tiene la app instalada, el sistema operativo abre la app directamente.

### Datos de la app
| | iOS | Android |
|---|---|---|
| **ID** | `org.techoapp.mitecho` | `com.techoapp.mitecho` |
| **Team/Cert** | Team ID: `2WN59DZ37K` | SHA-256: `FA:28:B6:...` |
| **Scheme** | `mitecho://` | `mitecho://` |

---

## Donaciones (Stripe)

> 🔒 Todos los endpoints requieren Bearer Token, excepto el webhook.

El sistema soporta dos modos: **pago único** (PaymentIntent) y **recurrente** (Subscription).  
Stripe habilita automáticamente todos los métodos de pago configurados en el Dashboard de la cuenta (`automatic_payment_methods: enabled`), sin restricción server-side: tarjeta, Apple Pay, Google Pay, PIX, Link, etc.

### `POST /donations/stripe/payment-intent` 🔒

Crea un PaymentIntent para una donación única. El `client_secret` se usa en el cliente (Stripe.js / SDK mobile) para confirmar el pago.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `amount` | integer | ✅ | Monto en unidades menores (ej: `1500` = $15.00) |
| `currency` | string | ✅ | ISO 4217 minúsculas (`usd`, `ars`, `brl`, etc.) |
| `source` | string | ✅ | Origen: `login_us` \| `home_pill` \| `profile` |
| `mode` | string | | `one_time` (default) \| `recurring` |
| `paymentMethod` | string | | `card` \| `apple_pay` \| `google_pay` \| `pix` — solo se guarda como metadata |
| `interval` | string | Si `mode=recurring` | `month` \| `year` |
| `countryCode` | string | | Código de país (ej: `AR`, `US`) |
| `idempotencyKey` | string | | Clave de idempotencia. Se genera automáticamente si se omite |

**Response 200**
```json
{
  "client_secret": "pi_xxx_secret_yyy",
  "intent_id": "pi_xxx"
}
```

---

### `POST /donations/stripe/subscription` 🔒

Crea una Subscription recurrente en Stripe. El `client_secret` devuelto corresponde al PaymentIntent del **primer cobro**, que debe confirmarse con el SDK de Stripe en el cliente. Los cobros siguientes son automáticos y se rastrean vía webhooks.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `amount` | integer | ✅ | Monto en unidades menores |
| `currency` | string | ✅ | ISO 4217 minúsculas |
| `source` | string | ✅ | `login_us` \| `home_pill` \| `profile` |
| `mode` | string | ✅ | Debe ser `recurring` |
| `interval` | string | ✅ | `month` \| `year` |
| `countryCode` | string | | Código de país |
| `idempotencyKey` | string | | Clave de idempotencia |

**Response 200**
```json
{
  "client_secret": "pi_xxx_secret_yyy",
  "subscription_id": "sub_xxx",
  "status": "incomplete"
}
```

El status inicial siempre es `incomplete`. Pasa a `active` una vez que se confirma el primer pago.

---

### `GET /donations/stripe/subscription/{subscriptionId}/status` 🔒

Devuelve el estado actual de una suscripción desde la base de datos local (sin llamar a Stripe).

**Response 200**
```json
{
  "subscription_id": "sub_xxx",
  "status": "active",
  "amount": 2500,
  "currency": "usd",
  "interval": "month",
  "current_period_end": "2026-06-19T12:04:00+00:00",
  "canceled_at": null
}
```

**Valores de `status`**

| Status | Descripción |
|---|---|
| `incomplete` | Primer pago aún no confirmado |
| `incomplete_expired` | Primer pago no completado en ~23h (terminal) |
| `active` | Suscripción activa y al día |
| `past_due` | Cobro fallido, Stripe reintentará |
| `canceled` | Cancelada (terminal) |
| `unpaid` | Reintentos agotados sin pago |

---

### `GET /donations/{intentId}/status` 🔒

Devuelve el estado de una donación única por su PaymentIntent ID.

**Response 200**
```json
{
  "intent_id": "pi_xxx",
  "status": "succeeded",
  "amount": 1500,
  "currency": "usd",
  "paid_at": "2026-05-19T12:04:00+00:00"
}
```

**Valores de `status`**: `pending` | `succeeded` | `failed` | `canceled`

---

### `POST /donations/stripe/webhook` (sin auth)

Endpoint para eventos de Stripe. La autenticidad se valida con la firma HMAC (`Stripe-Signature` header). Siempre responde `200` para evitar reintentos.

Eventos manejados:

| Evento | Efecto |
|---|---|
| `payment_intent.succeeded` | Donación → `succeeded`, guarda `paid_at` |
| `payment_intent.payment_failed` | Donación → `failed` |
| `payment_intent.canceled` | Donación → `canceled` |
| `invoice.paid` | Suscripción → `active`, actualiza `current_period_end` |
| `invoice.payment_failed` | Suscripción → `past_due` |
| `customer.subscription.updated` | Sincroniza status y período |
| `customer.subscription.deleted` | Suscripción → `canceled` |

---

## Notas generales

- 🔒 = requiere `Authorization: Bearer {token}`
- Los endpoints de inscripción disparan automáticamente **notificaciones push** (OneSignal) y **email** al usuario según el estado resultante.
- El payload de las notificaciones push incluye `idActividad` para que la app pueda navegar con `mitecho://actividades/{idActividad}`.
- Los tokens se obtienen de los endpoints de login y son de tipo **Laravel Passport / Sanctum**.
