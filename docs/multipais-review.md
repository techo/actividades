# Revisión a fondo: scoping multi-país (`idPaisPermitido`)

> Fecha: 2026-06-17 · Contexto: el sistema nació mono-país (Argentina) y creció a multi-país. Esta revisión audita el patrón de scoping por país en el backoffice.

---

## 1. Tres conceptos de "país" que conviven (y se confunden)

| Campo | Qué significa | Quién lo setea | Dónde se usa |
|---|---|---|---|
| `Persona.idPais` | País de **residencia** del usuario | Registro (api/web) | Datos del perfil |
| `Persona.idPaisPermitido` | País que el usuario **puede administrar** (permiso) | Casi nadie (ver §2) | Scoping del backoffice (~50 lugares) |
| `config('app.pais')` / `Session('pais')` | País **activo de la navegación** (qué contenido público mostrar) | Middlewares (`SeleccionarPais`, `UrlPais`) | Frontend público |

Son tres cosas distintas que se llaman parecido. El scoping del backoffice usa **`idPaisPermitido`**; el frontend público usa **`config('app.pais')`**. No se mezclan entre sí, pero el nombre confunde.

---

## 2. El problema de fondo: `idPaisPermitido` se *lee* en ~50 lugares pero casi nunca se *escribe*

**Se lee en ~50 sitios** (FormRequests, Policies, Searches, Exports, controllers). **Se escribe en solo 3 lugares:**

- `UserService` (alta de usuario de backoffice): si el rol es `admin`/`coordinador` → **hereda** `auth()->user()->idPaisPermitido` (el del que lo crea); si es `usuario_autenticado` → **0**.
- `CampanasController` (un caso) → 0.
- Default de la columna (migración 2023-04) → **0**.

**Consecuencias:**

- **El registro (api/web) NO setea `idPaisPermitido`** → todo usuario que se registra solo queda en **0**.
- El scope de un usuario de backoffice es **heredado** de quien lo creó. El admin raíz tiene que tener el valor seteado a mano (DB). Es una cadena frágil: si el primer admin de un país no lo tiene bien, todos los que cree heredan mal.
- **`0` es un sentinel mágico** con semántica ambigua:
  - En los `where idPais = idPaisPermitido` → `where idPais = 0` → **no matchea ningún país real** → listados vacíos.
  - En `CampanaPolicy::create` → `idPaisPermitido > 0` → 0 = no puede crear.
  - O sea 0 = "sin scope de país". Funciona, pero no es `null` ni está documentado.

---

## 3. El mismo "where" implementado de 3 formas distintas (la duplicación que mencionabas)

La regla **"el objeto es del mismo país que `idPaisPermitido` del usuario"** está copiada ~30 veces, con **tres estilos de comparación incompatibles**:

| Estilo | Dónde | Ejemplo |
|---|---|---|
| `==` (loose) | FormRequests | `$obj->idPais == $user->idPaisPermitido` |
| `!==` (strict) | Guards en controllers | `if ($actividad->idPais !== $user->idPaisPermitido)` |
| `(int) === (int)` (cast + strict) | `CampanaPolicy` | `(int)$user->idPaisPermitido === (int)$campaign->pais_id` |

**Por qué importa (bugs reales latentes):**

- **`==` loose es load-bearing en algunos lados**: `CrearActividad` compara `request()->input('idPais')` (string `"5"` del request) `== idPaisPermitido` (int `5`). Necesita loose para que `"5" == 5` sea true. Si alguien lo "arregla" a `===`, **rompe**.
- **`!==` strict es frágil al revés**: si por algún camino `idPais` llega como string y `idPaisPermitido` es int, `5 !== "5"` → true → **niega acceso indebidamente**.
- **`==` loose con nulls**: `0 == null` → **true** en PHP. Si un objeto tuviera `idPais = null` y el user `idPaisPermitido = 0`, daría "match" (acceso indebido). Hoy las columnas `idPais` son NOT NULL, así que es bajo riesgo — pero es una trampa.

El `(int) === (int)` de `CampanaPolicy` es el **más correcto** (normaliza tipos y compara estricto). Debería ser el estándar único.

---

## 4. No hay admin "global" (multi-país)

Incluso `hasRole('admin')` va **AND-eado** con `idPaisPermitido > 0` (`CampanaPolicy::create`). No existe un "ver/administrar todos los países": **cada usuario de backoffice está atado a UN país** vía `idPaisPermitido`. 

**Decisión del negocio (2026-06-17):** hoy NO se agrega, pero **podría haber un admin multi-país a futuro**. Implicancia de diseño: el refactor debe dejar **un único punto** (`Persona::administraPais()`, §6.1) donde mañana se agregue el bypass global con una sola línea (ej. `if ($this->esSuperAdmin()) return true;`), en vez de tener que volver a tocar los ~30 sitios. No se decide todavía si el "todos los países" será `idPaisPermitido === 0`, un rol nuevo, o un flag — pero el chokepoint queda listo para cualquiera de las opciones.

---

## 5. Inventario de los ~50 usos (por mecanismo)

- **FormRequest `authorize()`** (~13): CrearInscripcion, CrearPunto, CrearActividad, CrearCoordinador, CrearJornada, CrearHomeHeader, CrearLocalidad, DeleteLocalidad, UpdateComunidad, EditarComunidadFichaInicial, EditarInformeCierre, DeleteComunidad.
- **Policy** (CampanaPolicy ×4).
- **Search filters** (~9): Usuarios, Coordinadores, Comunidades, Equipos, InstitucionEducativa, Suscriptos, Oficinas, Provincias (+ LocalidadesData comentado).
- **Exports** (~4): CampanaSuscriptos, PersonasInscriptas, Suscriptos, Actividades.
- **Guards inline en controllers** (~12): ActividadesController (×2), ReportController (×4), EvaluacionesController, InscripcionesController, UsuariosController, InstitucionEducativa, CampanasController (ajax ×3).
- **Asignación de país desde el scope** (~3): ProvinciasController, InstitucionEducativaController (setean `id_pais = idPaisPermitido` al crear).

---

## 6. Recomendaciones (en orden)

1. **Una sola fuente de verdad para la regla.** Extraer un método único, p. ej. en `Persona`:
   ```php
   public function administraPais($idPais): bool {
       return (int) $this->idPaisPermitido === (int) $idPais;
   }
   ```
   y reemplazar los ~30 `where`/`if`/`authorize` inline por `auth()->user()->administraPais($obj->idPais)`. Normaliza el tipo (cast + estricto) y elimina la divergencia de operadores. **Para los FormRequest que comparan input del request** (`CrearActividad`, `CrearHomeHeader`), el cast a int hace que siga funcionando con strings.

2. **Definir la semántica de `0`** explícitamente (constante `Persona::SIN_PAIS = 0` o migrar a `null`) y documentarla. Decidir si `0` algún día significa "todos" para un rol global.

3. **Cerrar la asignación de `idPaisPermitido`.** Que el registro y/o el alta de usuario garanticen un valor coherente (¿= `idPais` de residencia para coordinadores? ¿0 para voluntarios?). Hoy depende de herencia frágil.

4. **Decidir el tema del admin global** con el negocio (§4).

5. **Tests de caracterización primero** (red de seguridad antes de tocar 30 sitios): hoy tenemos `PaisYLocaleTest` para el frontend; falta uno análogo para el **scoping de backoffice** (un coordinador de país A no puede tocar objetos de país B; uno con `idPaisPermitido` correcto sí). Eso ya lo ejercitan parcialmente los tests que arreglamos (crear actividad/punto/inscripción exigen país coincidente), pero conviene un test dedicado al método único.

---

## 7. Veredicto

**Funciona, pero es la zona más frágil del sistema** — exactamente el sedimento de la evolución mono→multi-país. No hay un bug abierto evidente (las columnas NOT NULL y los ints de DB tapan los riesgos del `==`/`!==`), pero:

- La regla está **duplicada ~30 veces** con 3 comparaciones distintas (deuda + riesgo al refactorizar).
- El linchpin (`idPaisPermitido`) tiene una **asignación frágil** y un **sentinel `0` sin documentar**.
- **No hay rol multi-país.**

Es refactor de **riesgo medio** por la cantidad de sitios, pero de bajo riesgo conceptual si se hace con la red de tests puesta y el método único. Recomiendo: (a) test de caracterización del scoping backoffice, (b) introducir `Persona::administraPais()`, (c) reemplazar los usos en tandas, corriendo la suite entre cada una.
