# API Reference — MiTECHO

Base URL: `https://{dominio}/api`

Autenticación: **Bearer Token** en el header `Authorization` para todas las rutas privadas (marcadas con 🔒).

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
- [Donaciones (Stripe)](#donaciones-stripe)
- [Datos maestros y ubicaciones](#datos-maestros-y-ubicaciones)
- [Traducciones](#traducciones)
- [Deep Linking](#deep-linking)

---

## Autenticación

### `POST /login`

Login con email y contraseña.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `mail` | string | ✅ | Email del usuario |
| `password` | string | ✅ | Contraseña |

**Response `200`**
```json
{ "access_token": "...", "token_type": "Bearer" }
```

---

### `POST /socialLogin`

Login con token de red social (Google / Facebook).

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `media` | string | ✅ | Red social (`google`, `facebook`) |
| `id` | string | ✅ | ID único del usuario en esa red social |
| `email` | string | ✅ | Email asociado a la cuenta |

**Response `200`** — `{ "access_token": "...", "token_type": "Bearer" }`

---

### `POST /providerLogin`

Login con proveedor externo (Apple, etc).

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `provider` | string | ✅ | Proveedor (`apple`, `google`) |
| `token` | string | ✅ | Token de autenticación del proveedor |

**Response `200`** — `{ "access_token": "...", "token_type": "Bearer" }`

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

Registro de nuevo voluntario. Ver validaciones en `CrearPersona`.

---

### `POST /create`

Creación de usuario.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `dni` | string | ✅ | Documento de identidad |
| `nombre` | string | ✅ | Nombre |
| `apellido` | string | ✅ | Apellido |
| `email` | string | ✅ | Email |
| `password` | string | ✅ | Contraseña |
| `fechaNacimiento` | string | | Fecha de nacimiento |
| `telefono` | string | | Con `+` codificado: `%2B5491123456789` |
| `recibirMails` | boolean | | Acepta comunicaciones |
| `acepta_marketing` | boolean | | Acepta marketing |
| `pais` | integer | | ID de país |
| `provincia` | integer | | ID de provincia |
| `localidad` | integer | | ID de localidad |
| `idUnidadOrganizacional` | integer | | ID de unidad organizacional |

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

Retorna los datos del usuario autenticado. Objeto `Persona` completo.

---

### `POST /ping` 🔒

Registra actividad reciente de la app. Actualiza `ultimo_acceso_app` con el timestamp actual.

> Llamar periódicamente mientras la app está en uso para trackear usuarios activos.

**Response `200`** — `{ "success": true }`

---

### `GET /personas/{id}` 🔒

Retorna los datos de una persona por su ID.

---

### `POST /editPersona/{id}` 🔒

Actualiza los datos de una persona (nombres, apellido, teléfono, etc).

---

### `POST /perfil/cambiar_photo` 🔒

Sube y actualiza la foto de perfil.

**Body** — `multipart/form-data`

| Campo | Tipo | Descripción |
|---|---|---|
| `photo` | file | Imagen de perfil |

---

### `DELETE /usuario` 🔒

Anonimiza la cuenta del usuario (soft delete de datos personales).

**Response `204`** — sin cuerpo.

---

## Actividades

### `GET /actividadesGeneral`

Lista de actividades públicas (sin autenticación).

**Query params**

| Param | Tipo | Descripción |
|---|---|---|
| `destacada` | int | `1` para solo destacadas |
| `pais` | int | Filtrar por ID de país |
| `tipos[]` | string | JSON de tipos de actividad (ver [Actividades por categoría](#actividades-por-categoría)) |

---

### `GET /actividades` 🔒

Lista de actividades para usuario autenticado. Mismos filtros que `/actividadesGeneral`.

---

### `GET /actividades/{id}` 🔒

Detalle de una actividad.

**Campos relevantes del response**

| Campo | Descripción |
|---|---|
| `estadoInscripcion` | `confirmed`, `confirm_by_paying`, `confirmation_date_is_closed`, `waiting_for_confirmation` |
| `ficha_medica` | Ficha médica cargada para la persona logueada |
| `estudios` | Estudios de la persona logueada |
| `inscriptos` | Inscriptos confirmados (solo si la persona está confirmada) |
| `coordinadores` | Coordinadores de la actividad |
| `pago` | Si la actividad tiene pago |
| `descripcionPago` | Descripción del pago |
| `pedidoBeca` | Link para solicitar beca (ej: formulario de Google) |
| `montoMin` | Monto mínimo de pago |
| `montoMax` | Monto máximo de pago |
| `linkQR` | Link QR de pago |

---

### `GET /actividades/categoria/{nombre}` 🔒

Actividades filtradas por categoría (shortcut del filtro por tipos).

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

### Actividades por categoría — URLs raw

Si necesitás pasar el filtro de tipos directamente (en lugar de usar el endpoint de categoría):

**Construcciones**
```
/api/actividades/?tipos[]=[{"idTipo":11},{"idTipo":27},{"idTipo":65},{"idTipo":72},{"idTipo":73},{"idTipo":80},{"idTipo":81},{"idTipo":98},{"idTipo":105},{"idTipo":114},{"idTipo":115}]
```

**Mesas de Trabajo**
```
/api/actividades/?tipos[]=[{"idTipo":25},{"idTipo":28},{"idTipo":29},{"idTipo":75},{"idTipo":76},{"idTipo":82},{"idTipo":83},{"idTipo":85},{"idTipo":113},{"idTipo":117}]
```

**Infraestructura**
```
/api/actividades/?tipos[]=[{"idTipo":22},{"idTipo":32},{"idTipo":77},{"idTipo":79},{"idTipo":97},{"idTipo":103}]
```

**Espacios Formativos**
```
/api/actividades/?tipos[]=[{"idTipo":23},{"idTipo":30},{"idTipo":31},{"idTipo":33},{"idTipo":34},{"idTipo":35},{"idTipo":36},{"idTipo":45},{"idTipo":46},{"idTipo":47},{"idTipo":49},{"idTipo":52},{"idTipo":53},{"idTipo":56},{"idTipo":58},{"idTipo":59},{"idTipo":62},{"idTipo":89}]
```

**Encuentros**
```
/api/actividades/?tipos[]=[{"idTipo":54},{"idTipo":55},{"idTipo":63},{"idTipo":64},{"idTipo":68},{"idTipo":69},{"idTipo":71},{"idTipo":86},{"idTipo":88},{"idTipo":90}]
```

**Colecta**
```
/api/actividades/?tipos[]=[{"idTipo":43},{"idTipo":96}]
```

**Eventos y Otros**
```
/api/actividades/?tipos[]=[{"idTipo":44},{"idTipo":48},{"idTipo":60},{"idTipo":101},{"idTipo":104}]
```

> Se puede combinar con `pais`, ej: `/api/actividades/?pais=33&tipos[]=[{"idTipo":11},{"idTipo":27}]`

---

## Inscripciones

### `GET /inscripciones` 🔒

Lista de inscripciones del usuario autenticado.

---

### `POST /inscripciones/actividad/{id}` 🔒

Inscribirse a una actividad.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `punto_encuentro` | integer | | ID del punto de encuentro seleccionado |
| `aceptar_terminos` | integer | | `1` si el usuario acepta los términos |
| `jornadas` | string (JSON) | | Array de jornadas: `[{"idJornada": 1, "selected": true}]` |
| `roles_aplicados` | string (JSON) | | Array de roles: `[{"id": 2}]` |
| `inscripciones_aplicadas` | string (JSON) | | Tipo: `secundario`, `corporativo`, `universitario` o `voluntario` |
| `respuestas` | string (JSON) | | Respuestas a preguntas: `[{"pregunta_id": 1, "respuesta": "Sí"}]` |

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

Subir comprobante de pago para una inscripción (transferencia o link de pago).

**Body** — `multipart/form-data`

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `idInscripcion` | integer | ✅ | ID de la inscripción |
| `voucher` | file | ✅ | Imagen del comprobante (jpg, jpeg, png, pdf) |

**Response `200`** — objeto `Inscripcion` con `voucherUrl` actualizado. También resetea `voucher_rechazado` y `voucher_rechazo_motivo` si el comprobante había sido rechazado previamente.

> El archivo se almacena en `storage/voucherInscipcion/{idPersona}/`. El typo histórico en el nombre del directorio es intencional — no corregir para no romper archivos existentes.

---

### `POST /inscripciones/voucher/clear` 🔒

Eliminar el comprobante subido. Solo funciona si el pago aún no fue confirmado (`pago != 1`).

**Body** — `application/json`

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `idInscripcion` | integer | ✅ | ID de la inscripción |

**Response `200`**
```json
{ "success": true }
```

**Response `403`** — si `pago == 1` (ya confirmado).

---

### `POST /inscripciones/beca` 🔒

Solicitar beca/exención de pago para una inscripción.

**Body** — `multipart/form-data`

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `idInscripcion` | integer | ✅ | ID de la inscripción |
| `reason` | string | ✅ | Motivo de la solicitud (max 3000 caracteres) |
| `evidence` | file | — | Comprobante opcional (jpg, jpeg, png, pdf, max 10 MB) |

**Response `200`**
```json
{ "success": true }
```

Guarda `scholarship_requested = true`, `scholarship_reason`, `scholarship_requested_at` y, si se adjunta archivo, `scholarship_evidence_url` en `storage/becaInscripcion/{idPersona}/`. El coordinador revisa desde el backoffice.

> `Actividad.beca` es distinto: es un link a un formulario externo (Google Forms, etc.). Los campos `scholarship_*` de `Inscripcion` son el flujo interno, independiente de ese link.

---

## Evaluaciones

### `GET /actividades/{id}/evaluaciones` 🔒

Datos para la pantalla de evaluación de una actividad.

**Response `200`**

| Campo | Descripción |
|---|---|
| `actividad` | Datos de la actividad |
| `listado_presentes` | Personas seleccionables para evaluar |
| `listado_a_evaluar` | Personas que deben mostrarse en el listado |
| `respuesta_actividad` | Evaluación de actividad si ya existe |
| `respuestas_persona` | Evaluaciones de personas si ya existen |
| `respuestas_impacto` | Evaluación de impacto si ya existe |
| `preguntasEvaluacionPersona` | Preguntas, descripción y tags positivos/negativos para personas |
| `preguntasEvaluacionImpacto` | Preguntas de impacto |

---

### `GET /actividades/{id}/evaluaciones/tags` 🔒

Tags disponibles para evaluar una actividad.

---

### `POST /actividades/{id}/evaluaciones` 🔒

Evaluar una actividad.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `idActividad` | integer | ✅ | ID de la actividad |
| `puntaje` | integer | ✅ | Puntaje del 1 al 10 |
| `tagsPositivos` | array | | Códigos de tags positivos |
| `tagsNegativos` | array | | Códigos de tags negativos |
| `comentario` | string | | Comentario libre |

---

### `POST /actividades/{id}/evaluaciones/impacto` 🔒

Registrar evaluación de impacto.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `idActividad` | integer | ✅ | ID de la actividad |
| `impacto_habilidades_capacidades` | integer | ✅ | Puntaje 1 a 10 |
| `impacto_percepcion_realidad` | integer | ✅ | Puntaje 1 a 10 |
| `impacto_recomendaria_experiencia` | integer | ✅ | Puntaje 1 a 10 |

---

### `POST /actividades/{id}/evaluaciones/persona/{idPersona}` 🔒

Evaluar a una persona dentro de una actividad.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `idActividad` | integer | ✅ | ID de la actividad |
| `evaluado` | object | ✅ | Objeto con `idPersona` |
| `puntajes` | object | ✅ | Puntajes por dimensión (ver abajo) |
| `tagsSeleccionados` | object | | Tags por dimensión (ver abajo) |
| `comentario` | string | | Comentario libre |

**Estructura de `puntajes`**
```json
{
  "conexion_equipo": "4",
  "compromiso_colaboracion": "7",
  "actitud_propositiva": "3",
  "potencia_otras": "5"
}
```

**Estructura de `tagsSeleccionados`**
```json
{
  "conexion_equipo": {
    "positivos": [],
    "negativos": ["evitar_suposiciones"]
  },
  "compromiso_colaboracion": {
    "positivos": ["resolucion_autonoma", "perseverancia"],
    "negativos": []
  },
  "actitud_propositiva": {
    "positivos": ["actitud_positiva"],
    "negativos": []
  },
  "potencia_otras": {
    "positivos": [],
    "negativos": ["ser_paciente"]
  }
}
```

---

## Perfil médico y estudios

### `GET /perfil/fichaMedica` 🔒

Obtener ficha médica del usuario.

---

### `POST /perfil/fichaMedica` 🔒

Crear o actualizar ficha médica.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `contacto_nombre` | string | | Nombre del contacto de emergencia |
| `contacto_telefono` | string | | Teléfono del contacto de emergencia |
| `contacto_relacion` | string | | Relación con el contacto |
| `grupo_sanguinieo` | string | | `A+`, `A-`, `AB+`, `AB-`, `B+`, `B-`, `O+`, `O-` |
| `cobertura_tipo` | string | | `cobertura_paga` o `salud_publica` |
| `cobertura_nombre` | string | Si `cobertura_tipo=cobertura_paga` | Nombre de la obra social |
| `cobertura_numero` | string | Si `cobertura_tipo=cobertura_paga` | Número de afiliado |
| `alergias` | string | | Descripción de alergias |
| `vacunacion_covid` | string | | `Si` o `No` |
| `alimentacion` | string | | Restricciones alimentarias |
| `confirma_datos` | boolean | ✅ | `1` para confirmar |

---

### `POST /perfil/fichaMedica/archivo_medico` 🔒

Subir archivo médico.

**Body** — `multipart/form-data`

| Campo | Tipo | Descripción |
|---|---|---|
| `documento_frente` | file | Frente del documento (opcional) |
| `documento_dorso` | file | Dorso del documento (opcional) |

---

### `GET /perfil/estudios` 🔒

Listar estudios del usuario.

---

### `POST /perfil/estudios` 🔒

Agregar un estudio.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `nivelDeEstudios` | string | ✅ | `secundario`, `universitario` o `posgrado` |
| `disciplina_academica` | string | | Disciplina académica |
| `idPersona` | integer | ✅ | Igual al idPersona del usuario logueado |
| `idPaisInstitucion` | integer | | País donde se realizó el estudio |
| `idInstitucionEducativa` | integer | | ID de institución (`0` si es "Otra") |
| `institucion_educativa` | string | Si `idInstitucionEducativa=0` | Nombre de la institución |
| `descripcion_educacion` | string | | Descripción del estudio |

---

### `PUT /perfil/estudios` 🔒

Actualizar un estudio. Mismos campos que el POST más:

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `id` | integer | ✅ | ID del estudio a editar |

---

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
|---|---|---|
| `pais_id` | int | Filtra por ID de país |
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

**Response `404`** si la campaña no existe o está inactiva.

---

### `POST /campanas/{id}/suscribir` 🔒

Suscribe al usuario autenticado a la campaña.

> La app **no necesita enviar datos personales** — el servidor los toma automáticamente del token.

**Body** (todo opcional)

| Campo | Tipo | Descripción |
|---|---|---|
| `respuestas` | array | Respuestas a preguntas dinámicas: `[{"pregunta_id": 1, "respuesta": "Sí"}]` |

**Response `200`**
```json
{
  "success": true,
  "message": "Gracias por inscribirte.",
  "whatsapp_link": "https://chat.whatsapp.com/...",
  "confirmation_message": "<p>¡Te esperamos!</p>"
}
```
> `whatsapp_link` y `confirmation_message` pueden ser `null`. Mostrar solo si no son `null`.

**Response `422`** — usuario ya inscripto:
```json
{
  "already_registered": true,
  "message": "Ya estás inscripto/a en esta campaña."
}
```

---

### `GET /campanas/{id}/suscripcion` 🔒

Verifica si el usuario autenticado ya está suscripto a la campaña.

**Response `200`**
```json
{ "inscripto": true }
```

---

## Dispositivos (Push)

Integración de notificaciones push via **OneSignal**.
El `player_id` es el identificador que genera el SDK de OneSignal en el dispositivo.

### `POST /dispositivos` 🔒

Registra o actualiza el dispositivo del usuario.

> Llamar después del login y cuando el SDK de OneSignal entregue un nuevo `player_id`.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `player_id` | string | ✅ | UUID generado por OneSignal |
| `plataforma` | string | | `ios` \| `android` \| `null` |

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

> Si el `player_id` ya existe, se reasigna al usuario actual y se reactiva automáticamente.

---

### `DELETE /dispositivos/{player_id}` 🔒

Desactiva el dispositivo al hacer logout. No elimina el registro, solo lo marca inactivo.

**Response `200`** — `{ "success": true }`

**Response `404`** si el `player_id` no pertenece al usuario autenticado.

---

## Donaciones (Stripe)

> 🔒 Todos los endpoints requieren Bearer Token, excepto el webhook.

El sistema soporta dos modos: **pago único** (PaymentIntent) y **recurrente** (Subscription).  
Stripe habilita automáticamente todos los métodos de pago configurados en el Dashboard (`automatic_payment_methods: enabled`): tarjeta, Apple Pay, Google Pay, PIX, Link, etc.

---

### Flujos de integración para la app mobile

#### 0) Config de checkout — montos sugeridos por país

Antes de iniciar cualquier flujo de pago, la app pide la configuración del país
del usuario autenticado (`Persona.idPais`): moneda local y tres montos sugeridos
(fijados por producto en BD, **no** por tipo de cambio).

```
GET /api/donations/stripe/checkout-config
Authorization: Bearer <token>

← 200 (ejemplo México):
{
  "id_pais": 146,
  "country_code": "MX",
  "currency": "mxn",
  "presets_major": { "bajo": 34, "medio": 85, "alto": 170 },
  "minor_unit_exponent": 2,
  "pix_enabled": false
}
```

- `presets_major` están en **unidad mayor**. Para llamar a Stripe convertir a
  unidad menor: `monto_minor = monto_major * 10^minor_unit_exponent`
  (ej. mxn `34 → 3400`; CLP tiene `minor_unit_exponent: 0`, así que `5000 → 5000`).
- Si el país del usuario no tiene montos configurados, se devuelve el **default
  global**: `currency: "usd"`, `presets_major: { bajo: 5, medio: 10, alto: 20 }`.
- `pix_enabled` indica si el país ofrece PIX (hoy solo Brasil).
- `country_code` sale del `iso2` del país; puede ser `null` si no está cargado.

---

#### A) Donación libre — pago único (card / Apple Pay / Google Pay)

```
1. POST /api/donations/stripe/payment-intent
   body: { amount, currency, source, mode: "one_time" }
   ← { client_secret, intent_id }

2. SDK Stripe (React Native / Flutter / iOS / Android)
   stripe.confirmPayment(client_secret, { type: 'Card', ... })
   ← PaymentIntent con status: 'succeeded' o 'requires_action'

3. Polling opcional (para confirmar el estado en el backend)
   GET /api/donations/{intent_id}/status
   ← { status: "succeeded", paid_at: "..." }

   ⚠️  No dependas solo del resultado del SDK. El estado definitivo
       llega vía webhook → el backend ya actualiza la tabla donations.
       Si el usuario cierra la app antes de recibir respuesta del SDK,
       el polling resuelve el estado correcto.
```

---

#### B) Donación libre — PIX

```
1. POST /api/donations/stripe/payment-intent
   body: { amount, currency: "brl", source, paymentMethod: "pix" }
   ← { client_secret, intent_id,
       pix: { copy_paste_code, qr_code_url, expires_at } }

   El PI ya viene confirmado — mostrar el QR directamente.
   No llamar al SDK de Stripe para PIX.

2. Polling hasta que el usuario pague en su banco
   GET /api/donations/{intent_id}/status
   ← { status: "pending" }  →  seguir esperando
   ← { status: "succeeded" } →  mostrar confirmación

   ⏱  PIX expira en 24 hs. Si expires_at pasó y status sigue
      "pending", mostrar opción de generar nuevo PIX.
```

---

#### C) Donación recurrente (suscripción)

```
1. POST /api/donations/stripe/subscription
   body: { amount, currency, source, mode: "recurring", interval: "month" }
   ← { client_secret, subscription_id, status: "incomplete" }

2. SDK Stripe — confirmar el PRIMER cobro (igual que pago único)
   stripe.confirmPayment(client_secret, { type: 'Card', ... })
   ← status: 'succeeded'

   Los cobros siguientes son automáticos — Stripe los cobra
   sin intervención del usuario ni de la app.

3. Polling del estado de la suscripción
   GET /api/donations/stripe/subscription/{subscription_id}/status
   ← { status: "active", current_period_end: "..." }

4. Listar suscripciones activas del usuario
   GET /api/donations/stripe/subscription
   ← { data: [ { subscription_id, status, amount, cancel_at_period_end, ... } ] }

5. Cambiar el monto / intervalo (input libre en la app)
   PATCH /api/donations/stripe/subscription/{subscription_id}
   body: { amount, interval }
   ← { subscription_id, status, amount, interval, current_period_end, ... }
   El nuevo monto aplica desde el próximo ciclo (sin prorrateo).

6. Autogestión (cambiar medio de pago / cancelar)
   POST /api/donations/stripe/billing-portal
   body: { return_url: "mitecho://stripe-billing-portal-return" }
   ← { url: "https://billing.stripe.com/session/..." }
   Abrir la URL en el navegador. Los cambios se reflejan vía webhooks.
```

---

#### D) Pago de inscripción a actividad (mobile)

```
1. POST /api/inscripciones/{idInscripcion}/stripe/payment-intent
   body: {} (monto y moneda vienen de la actividad)
   ← { client_secret, intent_id, amount, currency }

2. SDK Stripe — confirmar el pago
   stripe.confirmPayment(client_secret, { type: 'Card', ... })

3. Resultado del SDK:
   ✅ succeeded  → mostrar pantalla de confirmación
   ❌ failed     → mostrar error y opción de reintentar
   ⏳ requires_action → el SDK maneja 3D Secure automáticamente

   La inscripción queda marcada como pagada (pago = 1)
   vía webhook — no es necesario llamar a ningún endpoint extra.

4. Verificación opcional
   GET /api/inscripciones  (endpoint existente)
   El campo `pago` de la inscripción refleja el estado real.
```

---

#### Manejo de errores del SDK — referencia rápida

| Resultado SDK | Qué hacer |
|---|---|
| `succeeded` | Mostrar confirmación, actualizar UI |
| `requires_payment_method` | Tarjeta rechazada — pedir otro método |
| `requires_action` | 3DS — el SDK lo maneja solo, esperar resultado |
| `canceled` | Usuario canceló — volver al formulario |
| Error de red | Reintentar con el **mismo** `client_secret` (no crear nuevo PI) |

> **Idempotencia**: si el usuario cierra la app y vuelve a intentar, enviá el mismo `idempotencyKey` que la primera vez. El servidor devuelve el mismo PI existente en lugar de crear uno duplicado.

---

### `POST /donations/stripe/payment-intent` 🔒

Crea un PaymentIntent para una donación única.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `amount` | integer | ✅ | Monto en unidades menores (ej: `1500` = $15.00) |
| `currency` | string | ✅ | ISO 4217 minúsculas (`usd`, `ars`, `brl`, etc.) |
| `source` | string | ✅ | Origen: `login_us` \| `home_pill` \| `profile` |
| `mode` | string | | `one_time` (default) \| `recurring` |
| `paymentMethod` | string | | `card` \| `apple_pay` \| `google_pay` \| `pix` |
| `interval` | string | Si `mode=recurring` | `month` \| `year` |
| `countryCode` | string | | Código de país (ej: `AR`, `US`) |
| `idempotencyKey` | string | | Se genera automáticamente si se omite |

**Response `200` — métodos estándar (card, Apple Pay, Google Pay)**
```json
{
  "intent_id": "pi_xxx",
  "client_secret": "pi_xxx_secret_yyy"
}
```

**Response `200` — PIX**

El PaymentIntent se confirma automáticamente en el servidor. El QR code y código copia-pega están disponibles de inmediato.

```json
{
  "intent_id": "pi_xxx",
  "client_secret": "pi_xxx_secret_yyy",
  "pix": {
    "copy_paste_code": "00020126...",
    "qr_code_url": "https://qr.stripe.com/...",
    "expires_at": "2026-05-27T12:00:00Z"
  }
}
```

> Para card/wallets el cliente confirma con el SDK de Stripe. Para PIX el pago ya está en curso — solo mostrar el QR y esperar el webhook `payment_intent.succeeded`.

---

### `GET /donations/stripe/subscription` 🔒

Lista las suscripciones activas (no terminales) del usuario autenticado. Lee solo la base de datos local, sin llamar a Stripe.

**Response `200`**
```json
{
  "data": [
    {
      "subscription_id": "sub_xxx",
      "status": "active",
      "amount": 2500,
      "currency": "usd",
      "interval": "month",
      "current_period_end": "2026-07-08T12:00:00+00:00",
      "cancel_at_period_end": false,
      "canceled_at": null,
      "created_at": "2026-06-08T12:00:00+00:00"
    }
  ]
}
```

Devuelve `data: []` si no hay suscripciones. Excluye estados terminales (`canceled`, `incomplete_expired`).

> `cancel_at_period_end` (boolean): `true` cuando el usuario programó la cancelación desde el Customer Portal. La suscripción sigue `active` hasta `current_period_end` y luego se cancela. Se sincroniza vía webhook `customer.subscription.updated`.

---

### `PATCH /donations/stripe/subscription/{subscriptionId}` 🔒

Cambia el monto y/o el intervalo de una suscripción existente. Input libre, mismo rango que al crear. El nuevo monto **aplica desde el próximo ciclo** (sin prorrateo). La **moneda no cambia** (Stripe no lo permite) y se mantiene la de la suscripción.

**Path** — `subscriptionId`: Stripe Subscription ID (`sub_...`).

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `amount` | integer | ✅ | Nuevo monto en unidad menor (mismo rango que al crear) |
| `interval` | string | ✅ | `month` o `year` |
| `idempotencyKey` | string | | Evita aplicar dos veces en reintentos (se genera si se omite) |

**Response `200`** — misma forma que el item de `GET /donations/stripe/subscription`.
```json
{
  "subscription_id": "sub_xxx",
  "status": "active",
  "amount": 3500,
  "currency": "usd",
  "interval": "month",
  "current_period_end": "2026-07-08T12:00:00+00:00",
  "cancel_at_period_end": false,
  "canceled_at": null,
  "created_at": "2026-06-08T12:00:00+00:00"
}
```

**Errores**

| Código | Cuerpo | Motivo |
|--------|--------|--------|
| `404` | `{ "message": "Subscription not found." }` | No existe o no pertenece al usuario |
| `422` | `{ "errors": { "interval": [...] } }` | `interval` inválido (o `amount` fuera de rango) |
| `422` | `{ "code": "subscription_unchanged", "message": "subscription_unchanged" }` | `amount` + `interval` iguales a los actuales |
| `422` | `{ "code": "subscription_not_editable", "message": "subscription_not_editable" }` | Estado no editable (solo `active` / `past_due`) |
| `502` | `{ "message": "Could not update subscription.", "error": "..." }` | Falla en Stripe |

> Internamente crea un `Price` nuevo (los precios de Stripe son inmutables) y repunta el item de la suscripción. El cambio se refleja en la BD en el mismo request; el webhook `customer.subscription.updated` re-sincroniza de forma idempotente.

---

### `POST /donations/stripe/billing-portal` 🔒

Crea una sesión del Stripe Customer Portal y devuelve la URL para abrirla en el navegador. Desde el portal el usuario puede actualizar el medio de pago o cancelar sus donaciones recurrentes; los cambios se reflejan en la base local vía los webhooks de suscripción.

Requiere que el Customer Portal esté activado/configurado en el Dashboard de Stripe (cuenta de donaciones).

**Body (opcional)**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `return_url` | string | | URL de retorno al salir del portal. También acepta `returnUrl`. Default: `mitecho://stripe-billing-portal-return` |

> El `return_url` se valida como string (no como URL) para permitir deep links con scheme custom. Tener en cuenta que Stripe puede rechazar schemes no http(s); en ese caso usar una Universal Link / App Link https que redirija a la app.

**Response `200`**
```json
{
  "url": "https://billing.stripe.com/session/..."
}
```

**Errores**

| Código | Causa |
|---|---|
| `404` | El usuario no tiene Stripe customer (nunca inició una suscripción) |
| `502` | Stripe no pudo crear la sesión del portal |

---

### `POST /donations/stripe/subscription` 🔒

Crea una Subscription recurrente en Stripe.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `amount` | integer | ✅ | Monto en unidades menores |
| `currency` | string | ✅ | ISO 4217 minúsculas |
| `source` | string | ✅ | `login_us` \| `home_pill` \| `profile` |
| `mode` | string | ✅ | Debe ser `recurring` |
| `interval` | string | ✅ | `month` \| `year` |
| `countryCode` | string | | Código de país |
| `idempotencyKey` | string | | Se genera automáticamente si se omite |

**Response `200`**
```json
{
  "client_secret": "pi_xxx_secret_yyy",
  "subscription_id": "sub_xxx",
  "status": "incomplete"
}
```

> El status inicial siempre es `incomplete`. Pasa a `active` cuando se confirma el primer pago con el SDK.

---

### `GET /donations/stripe/subscription/{subscriptionId}/status` 🔒

Devuelve el estado actual de una suscripción desde la base de datos local.

**Response `200`**
```json
{
  "subscription_id": "sub_xxx",
  "status": "active",
  "amount": 2500,
  "currency": "usd",
  "interval": "month",
  "current_period_end": "2026-06-19T12:04:00+00:00",
  "cancel_at_period_end": false,
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

**Response `200`**
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

### `GET /donations/history` 🔒

Historial unificado de pagos únicos y suscripciones, ordenado por fecha descendente.

**Query params (todos opcionales)**

| Param | Ejemplo | Descripción |
|---|---|---|
| `type` | `one_time` \| `subscription` | Filtrar por tipo |
| `status` | `succeeded` | Filtrar por estado |
| `from` | `2026-01-01` | Desde fecha (ISO) |
| `limit` | `20` | Items por página (máx 100, default 20) |
| `page` | `1` | Número de página |

**Response `200`**
```json
{
  "data": [
    {
      "type": "one_time",
      "id": "pi_xxx",
      "amount": 150000,
      "currency": "ars",
      "status": "succeeded",
      "source": "inscripcion",
      "payment_method": "card",
      "inscripcion_id": 42,
      "actividad": { "id": 17, "nombre": "Construcción Mar del Plata" },
      "paid_at": "2026-05-19T12:04:00Z",
      "stripe_receipt_url": "https://pay.stripe.com/receipts/...",
      "created_at": "2026-05-19T11:58:00Z"
    },
    {
      "type": "subscription",
      "id": "sub_zzz",
      "amount": 1000,
      "currency": "brl",
      "status": "active",
      "source": "home_pill",
      "interval": "month",
      "inscripcion_id": null,
      "actividad": null,
      "stripe_receipt_url": null,
      "current_period_end": "2026-07-08T00:00:00Z",
      "canceled_at": null,
      "created_at": "2026-06-08T12:00:00Z"
    }
  ],
  "meta": { "total": 2, "page": 1, "limit": 20 }
}
```

> `stripe_receipt_url`: URL del recibo de Stripe. Se persiste al confirmarse el pago (webhook `payment_intent.succeeded`, donde vive en el charge). En items `one_time` puede ser `null` si el pago aún no se confirmó o si la donación es anterior a este campo (backfill pendiente). En items `subscription` es siempre `null` — los recibos de una suscripción son por cobro (invoice), no por suscripción.

---

### `POST /inscripciones/{idInscripcion}/stripe/payment-intent` 🔒

Crea un PaymentIntent para pagar una inscripción desde la app mobile. Usa la clave Stripe del país de la actividad. El monto y moneda se toman de la actividad — no se pasan en el body.

**Body**

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `idempotencyKey` | string | | Se genera automáticamente si se omite |

**Response `200`**
```json
{
  "client_secret": "pi_xxx_secret_yyy",
  "intent_id": "pi_xxx",
  "amount": 150000,
  "currency": "ars"
}
```

**Flujo**
1. App llama este endpoint → recibe `client_secret`
2. App confirma con el SDK nativo de Stripe (`stripe.confirmPayment`)
3. Stripe dispara `payment_intent.succeeded` al webhook `/stripe/webhook/{paisId}`
4. Webhook marca `Inscripcion.pago = 1`, actualiza el `Donation` vinculado y envía email de confirmación

---

### `POST /donations/stripe/webhook` (sin auth)

Endpoint para eventos de Stripe. La autenticidad se valida con la firma HMAC (`Stripe-Signature` header). Siempre responde `200` para evitar reintentos.

**Eventos manejados**

| Evento | Efecto |
|---|---|
| `payment_intent.succeeded` | Donación → `succeeded`, guarda `paid_at` |
| `payment_intent.payment_failed` | Donación → `failed` |
| `payment_intent.canceled` | Donación → `canceled` |
| `invoice.paid` | Suscripción → `active`, actualiza `current_period_end` |
| `invoice.payment_failed` | Suscripción → `past_due` |
| `customer.subscription.updated` | Sincroniza `status`, `current_period_end`, `amount` y `cancel_at_period_end` (ignora suscripciones ya terminales) |
| `customer.subscription.deleted` | Suscripción → `canceled`, guarda `canceled_at` |

---

## Datos maestros y ubicaciones

### `GET /categorias`

Lista de categorías de actividades.

### `GET /sedes`

Lista de sedes/oficinas.

---

### `GET /paises`

Lista de países (también disponible en `/ajax/paises`).

### `GET /paises/habilitados`

Lista de países habilitados en el sistema (también en `/ajax/paises/habilitados`).

### `GET /paises/{id_pais}/provincias`

Provincias de un país (también en `/ajax/paises/{id}/provincias`).

**Ejemplo:** `/ajax/paises/13/provincias`

### `GET /paises/{id_pais}/provincias/{id_provincia}/localidades`

Localidades de una provincia (también en `/ajax/paises/{id}/provincias/{id}/localidades`).

**Ejemplo:** `/ajax/paises/13/provincias/1/localidades`

---

## Traducciones

### `GET /translate`

Obtener una traducción.

**Query params**

| Param | Ejemplo | Descripción |
|---|---|---|
| `code` | `frontend.name` | Código de diccionario (prefijo `frontend.`) |
| `lang` | `es_AR` | Idioma: `es_AR`, `es_CH`, `pt`, `en` |

**Ejemplo:** `/api/translate?code=frontend.name&lang=es_AR`

---

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

Cuando el usuario toca un link `https://actividades.techo.org/actividades/{id}` y tiene la app instalada, el sistema operativo la abre directamente.

### Datos de la app

| | iOS | Android |
|---|---|---|
| **ID** | `org.techoapp.mitecho` | `com.techoapp.mitecho` |
| **Team/Cert** | Team ID: `2WN59DZ37K` | SHA-256: `FA:28:B6:...` |
| **Scheme** | `mitecho://` | `mitecho://` |

---

## Notas generales

- 🔒 = requiere `Authorization: Bearer {token}`
- Los endpoints de inscripción disparan automáticamente **notificaciones push** (OneSignal) y **email** al usuario según el estado resultante.
- El payload de las notificaciones push incluye `idActividad` para que la app pueda navegar con `mitecho://actividades/{idActividad}`.
- Los tokens se obtienen de los endpoints de login y son de tipo **Laravel Passport**.
