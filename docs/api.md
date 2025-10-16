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
### Logout
- **URL:** `/api/logout`  
- **Método:** `GET`  
- **Headers:** `Authorization: Bearer {token}`  
- **Devuelve:** Cierra la sesión del usuario autenticado  

---

### Registro
- **URL:** `/api/create`  
- **Método:** `GET` _(lo ideal sería `POST`)_  
- **Parámetros principales:**  
- `dni`, `nombres`, `apellidoPaterno`  
- `mail`  
- `password`  
- `fechaNacimiento`  
- `telefono` (⚠️ con `+`, ej: `%2B5491123456789`)  
- `recibirMails`, `acepta_marketing`  
- `idPais`, `idProvincia`, `idLocalidad`, `idUnidadOrganizacional`  
- **Ejemplo:**  
/api/create?dni=123456&nombres=juio&apellidoPaterno=zao&mail=juios@mail.com&password=juju1234&telefono=%2B5491123456789&idPais=13&idProvincia=1&idLocalidad=22

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
- `cobertura_nombre` (texto)  
- `cobertura_numero` (texto)  
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
- `archivo_medico` (archivo de imagen, opcional)  
- `documento_frente` (archivo de imagen, opcional)  
- `documento_dorso` (archivo de imagen, opcional)  
- **Headers:** `Authorization: Bearer {token}`  

---

## 📅 Actividades

### Listado general
- **URL:** `/api/actividades/`  
- **Método:** `GET`  
- **Headers:** (Opcional) `Authorization: Bearer {token}`  
- **Parámetros opcionales:**  
- `destacada=1` → actividades destacadas  
- `pais={id}` → filtra por país  
- `tipos[]={json}` → lista de tipos de actividad  

---

### Actividad puntual
- **URL:** `/api/actividades/{id}`  
- **Ejemplo:**  
/api/actividades/18402
- **Devuelve:** Datos completos de la actividad  

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
