# 🌐 Documentación API

Este documento describe los endpoints principales de la API.  

---

## 🔑 Autenticación

### Login
- **URL:** `/api/login`  
- **Método:** `POST`
- **Parámetros:**  
  - `user` → email de usuario  
  - `password` → contraseña  
- **Ejemplo:**  
/api/login?user=agustin.vilas@techo.org&password=qwer1234

- **Devuelve:** Token de autenticación  

---
### Login Social
- **URL:** `/api/socialLogin`  
- **Método:** `POST`  
- **Parámetros:**  
  - `media` → red social utilizada (por ejemplo: google, facebook)  
  - `id` → identificador único del usuario en esa red social  
  - `email` → correo electrónico asociado a la cuenta  

- **Devuelve:** Token de autenticación 

---
### Login Social por Provider (NEW)
- **URL:** `/api/providerLogin`  
- **Método:** `POST`  
- **Parámetros:**  
  - `provider` → red social utilizada (por ejemplo: google, facebook)  
  - `token` → identificador único enviado al hacer login  

- **Devuelve:** Token de autenticación 
 
---

### Logout
- **URL:** `/api/logout`  
- **Método:** `GET`  
- **Headers:** `Authorization: Bearer {token}`  
- **Devuelve:** Cierra la sesión del usuario autenticado  

---

### Reset Password
- **URL:** `/api/password/reset`  
- **Método:** `POST`  
- **Headers:** `Authorization: Bearer {token}`  
- **Devuelve:** Envia mail de reset password

---

### Delete Usuario
- **URL:** `/api/usuario`  
- **Método:** `DELETE`  
- **Headers:** `Authorization: Bearer {token}`  
- **Devuelve:** Elimina el usuario logueado 

---

### Registro
- **URL:** `/api/create`  
- **Método:** `POST`  
- **Parámetros principales:**  
- `dni`, `nombre`, `apellido`  
- `email`  
- `password`  
- `fechaNacimiento`  
- `telefono` (⚠️ con `+`, ej: `%2B5491123456789`)  
- `recibirMails`, `acepta_marketing`  
- `pais`, `provincia`, `localidad`, `idUnidadOrganizacional`  
- **Ejemplo:**  
/api/create?dni=123456&nombre=juio&apellido=zao&email=juios@mail.com&password=juju1234&telefono=%2B5491123456789&pais=13&provincia=1&localidad=22

- **Devuelve:** Registro de usuario o error de validación  

---

## 👤 Perfil

### Cambiar foto de perfil
- **URL:** `/api/perfil/cambiar_photo`  
- **Método:** `POST`  
- **Parámetros:**  
- `photo` (archivo de imagen)  
- **Headers:** `Authorization: Bearer {token}`  

---

### Modificar o Crear Ficha Medica
- **URL:** `/api/perfil/fichaMedica/?contacto_nombre=jorge&contacto_telefono=1233321&contacto_relacion=familiar&grupo_sanguinieo=a+&cobertura_tipo=social&cobertura_nombre=nombre cobert&cobertura_numero=1233312312&alergias=si tengo&vacunacion_covid=si&alimentacion=asdas&confirma_datos=1`  
- **Método:** `POST`  
- **Parámetros:**  
- `contacto_nombre` (texto)  
- `contacto_telefono` (texto)  
- `contacto_relacion` (texto)  
- `grupo_sanguinieo` (Valores: A+, A-, AB+, AB-, B+, B-, O+, 0-)  
- `cobertura_tipo` (Valores: cobertura_paga, salud_publica)
- `cobertura_nombre` (texto, solo si cobertura_tipo = 'cobertura_paga')  
- `cobertura_numero` (texto, solo si cobertura_tipo = 'cobertura_paga')   
- `alergias` (texto)  
- `vacunacion_covid` (Valores : Si , No)  
- `alimentacion` (texto)  
- `confirma_datos` (Boolean: 1)  
- **Headers:** `Authorization: Bearer {token}`  

---

### Cambiar archivos Ficha Medica
- **URL:** `/api/perfil/fichaMedica/archivo_medico`  
- **Método:** `POST`  
- **Parámetros:**  
- `documento_frente` (archivo de imagen, opcional)  
- `documento_dorso` (archivo de imagen, opcional)  
- **Headers:** `Authorization: Bearer {token}`  

---

### Ficha Medica
- **URL:** `/api/perfil/fichaMedica/`  
- **Método:** `GET`  
- **Headers:** `Authorization: Bearer {token}`  

------
---

### Estudios
- **URL:** `/api/perfil/estudios/`  
- **Método:** `GET`  
- **Headers:** `Authorization: Bearer {token}`  

------

### Crear Estudio
- **URL:** `/api/perfil/estudios/`  
- **Método:** `POST`  
- **Parámetros:**  
- `nivelDeEstudios` (Valores aceptados: secundario, universitario o posgrado)  
- `disciplina_academica` (Disciplina académica, texto)  
- `idPersona` (Igual al idPersona logueado)  
- `idPaisInstitucion` (Pais donde se realizo estudio)  
- `idInstitucionEducativa` (ID institucion, 0 en caso de ser Otra)  
- `institucion_educativa` (Texto, en caso de no existir en bbdd la institucion)  
- `descripcion_educacion` (descripcion, texto)  
- **Headers:** `Authorization: Bearer {token}`  


------

### Editar Estudio
- **URL:** `/api/perfil/estudios/archivo_medico`  
- **Método:** `PUT`  
- **Parámetros:**  
- (repite parametros Crear Estudio)  
- `id` (idEstudio a editar)  
- **Headers:** `Authorization: Bearer {token}`  

---

### Insitutuciones Educativas por Pais
- **URL:** `/api/perfil/estudios/institucionEducativa/pais/{idPais}`  
- **Método:** `GET`  
- **Parámetros:**  
- `idPais` (en URL)  
- **Headers:** `Authorization: Bearer {token}`  

---
## 📅 Actividades

### Listado general
- **URL:** `/api/actividades/`  
- **Método:** `GET`  
- **Parámetros opcionales:**  
- `destacada=1` → actividades destacadas  
- `pais={id}` → filtra por país  
- `tipos[]={json}` → lista de tipos de actividad  

---
### Listado por categorias
- **URL:** `/api/actividades/`  
- **Método:** `GET`  
- **Headers:** (Opcional) `Authorization: Bearer {token}`  
- **Parámetros opcionales:**  
- `destacada=1` → actividades destacadas  
- `pais={id}` → filtra por país  
- `tipos[]={json}` → lista de tipos de actividad  

---

### Actividad show
- **URL:** `/api/actividades/{id}`  
- **Ejemplo:**  
/api/actividades/18402
- **Devuelve:**
 Datos completos de la actividad, se detallan algunos importantes
- `estadoInscripcion` →  confirmed, confirm_by_paying, confirmation_date_is_closed, waiting_for_confirmation
- `ficha_medica` → devuelve ficha medica cargada para la persona logueada
- `estudios` → estudios de la persona logueada  
- `inscriptos` → inscriptos confirmados a la actividad  (solo si persona confirmed)
- `coordinadores` → coordinadores de la actividad
- `pago` → si la actividad tiene pago
- `descripcionPago` 
- `pedidoBeca` → Link para solicitar beca (ej:form de google)
- `montoMin`
- `montoMax`
- `linkQR`
 
---

### Actividades destacadas
- **URL:** `/api/actividades/?destacada=1`  
- **Devuelve:** Solo las destacadas  

---

### Actividades por país
- **URL:** `/api/actividades/?pais={id}`  
- **Ejemplo:**  
/api/actividades/?pais=33

---

### Actividades por categoría

#### Construcciones
/api/actividades/?tipos[]=[{"idTipo":11},{"idTipo":27},{"idTipo":65},{"idTipo":72},{"idTipo":73},{"idTipo":80},{"idTipo":81},{"idTipo":98},{"idTipo":105},{"idTipo":114},{"idTipo":115}]

#### Mesas de Trabajo
/api/actividades/?tipos[]=[{"idTipo":25},{"idTipo":28},{"idTipo":29},{"idTipo":75},{"idTipo":76},{"idTipo":82},{"idTipo":83},{"idTipo":85},{"idTipo":113},{"idTipo":117}]

#### Infraestructura
/api/actividades/?tipos[]=[{"idTipo":22},{"idTipo":32},{"idTipo":77},{"idTipo":79},{"idTipo":97},{"idTipo":103}]

#### Espacios Formativos
/api/actividades/?tipos[]=[{"idTipo":23},{"idTipo":30},{"idTipo":31},{"idTipo":33},{"idTipo":34},{"idTipo":35},{"idTipo":36},{"idTipo":45},{"idTipo":46},{"idTipo":47},{"idTipo":49},{"idTipo":52},{"idTipo":53},{"idTipo":56},{"idTipo":58},{"idTipo":59},{"idTipo":62},{"idTipo":89}]

#### Encuentros
/api/actividades/?tipos[]=[{"idTipo":54},{"idTipo":55},{"idTipo":63},{"idTipo":64},{"idTipo":68},{"idTipo":69},{"idTipo":71},{"idTipo":86},{"idTipo":88},{"idTipo":90}]

#### Colecta
/api/actividades/?tipos[]=[{"idTipo":43},{"idTipo":96}]

#### Eventos y Otros
/api/actividades/?tipos[]=[{"idTipo":44},{"idTipo":48},{"idTipo":60},{"idTipo":101},{"idTipo":104}]

👉 Se puede combinar con `pais`, ej:  
/api/actividades/?pais=33&tipos[]=[{"idTipo":11},{"idTipo":27}]

---

## 📝 Inscripciones

### Mis inscripciones
- **URL:** `/api/inscripciones/`  
- **Método:** `GET`  
- **Headers:** `Authorization: Bearer {token}`  
- **Devuelve:** Actividades en las que el usuario está inscripto  

---

### Inscribir a Actividad

- **URL:** `/api/inscripciones/actividad/{idActividad}/`
- **Método:** `POST`
- **Parámetros:**
- `punto_encuentro` → ID del punto de encuentro seleccionado
- `aceptar_terminos` →  1 si el usuario acepta los términos y condiciones   
- `jornadas` →  Lista de jornadas seleccionadas (array vacío [] si no aplica)   
- `roles_aplicados` →  Lista de roles a los que el usuario aplica (array vacío [] si no aplica)   
- `inscripciones_aplicadas` →  secundario, corporativo, universitario o voluntario (solo estos valores)   
- **Devuelve:**
- `message` 
- `actividad_id` 
- `inscripcion_id`
- `estados_inscripcion` →  PRE_INSCRIPTO, FALTA_PAGO, CONFIRMADO, PUNTO_ENCUENTRO_CERRADO o FALTA_ACEPTAR_TERMINOS

### Ver Evaluacion por Actividad

- **URL:** `/api/actividades/{idActividad}/evaluaciones`
- **Método:** `GET`
- **Devuelve:**
- `actividad` 
- `listado_presentes` - personas que deberian ser seleccionables
- `listado_a_evaluar` - personas que deberia mostrarse en el listado
- `respuesta_actividad` - evaluacion de actividad, si la hubiera 
- `respuestas_persona` - evaluacion de impacto, si la hubiera 
- `respuestas_impacto` - evaluacion de las personas, si la hubiera 
- `preguntasEvaluacionPersona` - preguntas, descripcion y tags postitvos/negativos para personas
- `preguntasEvaluacionImpacto` - preguntas de impacto

### Enviar Evaluacion por Actividad

- **URL:** `/api/actividades/{idActividad}/evaluaciones`
- **Método:** `POST`
- **Devuelve:**
- `idActividad` 
- `puntaje` - 1 a 10
- `tagsPositivos` - array con codigo de los tags
- `tagsNegativos` - array 
- `comentario`

### Enviar Evaluacion por Impacto

- **URL:** `/api/actividades/{idActividad}/evaluaciones/impacto`
- **Método:** `POST`
- **Devuelve:**
- `idActividad` 
- `impacto_habilidades_capacidades` - 1 a 10
- `impacto_percepcion_realidad` - 1 a 10
- `impacto_recomendaria_experiencia` - 1 a 10

### Enviar Evaluacion por Persona

- **URL:** `/api/actividades/{idActividad}/evaluaciones/persona/{idPersona}`
- **Método:** `POST`
- **Devuelve:**
- `idActividad` 
- `evaluado` - array con atributos idPersona
- `puntajes` - 
array con puntajes, ej:
    {
      "conexion_equipo": "4",
      "compromiso_colaboracion": "7",
      "actitud_propositiva": "3",
      "potencia_otras": "5"
    }
- `tagsSeleccionados` - array de tags
  {
    "conexion_equipo": {
      "positivos": [],
      "negativos": [
        "evitar_suposiciones"
      ]
    },
    "compromiso_colaboracion": {
      "positivos": [
        "resolucion_autonoma",
        "perseverancia"
      ],
      "negativos": []
    },
    "actitud_propositiva": {
      "positivos": [
        "actitud_positiva"
      ],
      "negativos": []
    },
    "potencia_otras": {
      "positivos": [],
      "negativos": [
        "ser_paciente"
      ]
    }
  }
- `comentario` - string


### 📤 Subir Voucher Pago

- **URL:** `/api/inscripciones/voucher/`
- **Método:** `POST`
- **Parámetros:**
- `idInscripcion` 
- `voucher` →  imagen  
- **Devuelve:**
- `inscripcion.voucherURL` 


## 📣 Campañas

### Listado de campañas
- **URL:** `/api/campanas`
- **Método:** `GET`
- **Parámetros opcionales:**
  - `pais_id={id}` → filtra por país
  - `tipo=colecta` o `tipo=captacion`
  - `per_page={n}` → resultados por página (default 20, máx 50)
- **Devuelve:** Lista paginada de campañas activas con sus preguntas dinámicas

---

### Detalle de campaña
- **URL:** `/api/campanas/{id}`
- **Método:** `GET`
- **Devuelve:** Datos completos de la campaña, incluyendo:
  - `preguntas` → preguntas dinámicas del formulario de suscripción
  - `confirmation_message` → mensaje HTML a mostrar luego de suscribirse (puede ser `null`)
  - `whatsapp_link` → link al grupo de WhatsApp (puede ser `null`)

---

### Suscribir a campaña
- **URL:** `/api/campanas/{id}/suscribir`
- **Método:** `POST`
- **Headers:** `Authorization: Bearer {token}`
- **Parámetros opcionales:**
  - `respuestas` → array de respuestas a preguntas dinámicas:
    ```json
    [{ "pregunta_id": 1, "respuesta": "Sí" }]
    ```
- **Nota:** No es necesario enviar nombre, email ni teléfono. El servidor los toma del usuario autenticado.
- **Devuelve:**
  - `success` → `true`
  - `message` → mensaje de confirmación
  - `whatsapp_link` → link al grupo de WhatsApp o `null`
  - `confirmation_message` → HTML con mensaje personalizado o `null`
- **Error `422`:** si el usuario ya está inscripto → `{ "already_registered": true, "message": "..." }`

---

### Verificar suscripción
- **URL:** `/api/campanas/{id}/suscripcion`
- **Método:** `GET`
- **Headers:** `Authorization: Bearer {token}`
- **Devuelve:** `{ "inscripto": true }` o `{ "inscripto": false }`

---

## 🌍 Ubicaciones

### Paises
- **URL:** `/ajax/paises`  
- **Método:** `GET`  

### Paises habilitados (donde está Techo)
- **URL:** `/ajax/paises/habilitados`  
- **Método:** `GET`  

### Provincias
- **URL:** `/ajax/paises/{id}/provincias`  
- **Ejemplo:**  
/ajax/paises/13/provincias

### Localidades
- **URL:** `/ajax/paises/{id}/provincias/{id}/localidades`  
- **Ejemplo:**  
/ajax/paises/13/provincias/1/localidades

---

## 🏷️ Categorías
- **URL:** `/api/categorias`  
- **Método:** `GET`  
- **Devuelve:** Lista de categorías de actividades

## 🏷️ Traduccion
- **URL:** `/api/translate`  
- **Método:** `GET`  
- **Parámetros:**  
- `code=frontend.name` → codido de diccionario, anteponer frontend.
- `lang=es_AR` → lenguaje buscado (es_AR, es_CH, pt o en)   
- **Devuelve:** Texto Traduccion
- **Ejemplo:** /api/translate?code=frontend.name&lang=es_AR
