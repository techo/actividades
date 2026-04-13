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

## Notas generales

- 🔒 = requiere `Authorization: Bearer {token}`
- Los endpoints de inscripción disparan automáticamente **notificaciones push** (OneSignal) y **email** al usuario según el estado resultante.
- El payload de las notificaciones push incluye `idActividad` para que la app pueda navegar con `mitecho://actividades/{idActividad}`.
- Los tokens se obtienen de los endpoints de login y son de tipo **Laravel Passport / Sanctum**.
