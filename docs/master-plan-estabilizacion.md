# Master Plan de Estabilización — actividades.techo.org

**De sistema en producción a plataforma de 5-10 años. Julio 2026.**

> Este documento reemplaza la prioridad del informe anterior (`docs/arquitectura-ia-soporte-continuo.md`). Ese informe seguía siendo válido como diseño de un sistema de soporte con IA, pero partía de una premisa que este documento corrige: no tiene sentido construir inteligencia operativa sobre una base que hoy tiene vulnerabilidades activas de fraude de pago, cero red de CI confiable y deuda de integridad de datos sin resolver. Todo lo que sigue surge de un segundo recorrido exhaustivo del código, más profundo que el primero, buscando específicamente lo que el primer análisis no cubrió con el mismo nivel de detalle.

---

## 0. Postura frente a tu conclusión — coincido, con una corrección importante

Tu conclusión es correcta y la evidencia la respalda con más fuerza de la que tenías cuando la escribiste: este segundo recorrido encontró **tres vulnerabilidades de severidad crítica en producción** (fraude de pago falsificable en el flujo PayU, y dos IDOR que permiten leer y modificar datos de cualquier usuario) y **confirmación de que hoy no existe ningún pipeline de CI que ejecute los tests de forma confiable** — es decir, ni siquiera hay una red que hubiera detectado esos tres bugs si alguien los hubiera introducido ayer. Construir un sistema de agentes de IA que "aprenda" de este estado y empiece a generar fixes automáticos sería, literalmente, automatizar sobre un sistema donde ya sabemos que hay fraude posible y ningún gate lo frena. Tenías razón en frenar.

La corrección que quiero hacer, porque me pediste honestidad y no complacencia: **"fortalecer los cimientos" no debería traducirse en "congelar todo desarrollo de features durante meses."** Esto es un sistema en producción, con una ONG operando actividades reales y cobrando pagos reales todos los días — un freezing total tiene costo de oportunidad real (voluntarios, donantes, coordinadores esperando funcionalidad) y no es estrictamente necesario. Lo que sí es no negociable es esto: **antes de escribir una sola línea de feature nueva, hay que cerrar tres agujeros de seguridad activos (días, no meses) y levantar un gate de CI real (una semana, no meses)**. Una vez que ese gate existe, la consolidación y modernización pueden convivir con desarrollo de features nuevas en paralelo, porque el gate es justamente lo que evita que el feature nuevo agregue deuda sin que nadie lo note — que es, en el fondo, cómo se llegó a la situación actual. Divido el plan en dos velocidades, no en una pausa larga: una intervención de emergencia inmediata y acotada, y un roadmap de fondo que corre en paralelo al negocio, no en lugar de él.

También quiero señalar algo a tu favor que quizás no viste con esta claridad: **ya hay evidencia real, en este mismo repo, de que el enfoque de estabilización funciona cuando se hace bien.** Dos sesiones documentadas en `progress/history.md` (2026-06-15 y 2026-06-17) llevaron la suite de tests de ~19/97 a 97/97 verde y, en el camino, **corrigieron 6 bugs reales de producción** (fuga de locale en notificaciones push, filtro de país inconsistente en búsqueda de actividades, un `Persona::fusionar()` que dejaba coordinadores huérfanos, entre otros) — bugs que nadie sabía que existían hasta que se invirtió en arreglar la base de tests. Esa es la prueba empírica, no teórica, de que "invertir en fundamentos revela y arregla bugs reales que las features nuevas nunca iban a exponer por sí solas". No estás empezando de cero: ya hay tracción, y este plan la retoma y la ordena, no la reemplaza.

---

## 1. Pensar el proyecto como plataforma, no como aplicación

Una aplicación se mide por lo que hace hoy. Una plataforma se mide por qué tan barato es agregar la próxima cosa dentro de 3 años sin romper las anteriores. Aplicado a este código: hoy, agregar un país nuevo con un método de pago nuevo requiere tocar `PagosController`, `StripeController`, `api/InscripcionStripeController` y el JSON `config_pago` sin ningún schema documentado — cuatro lugares, sin garantía de consistencia. Agregar un tipo de actividad nuevo requiere editar un array hardcodeado dentro de una closure de ruta. Agregar un test de un flujo de pago nuevo no tiene dónde apoyarse, porque los flujos de pago existentes no tienen tests de referencia. Ese es el costo real de tratar esto como una aplicación que "ya funciona" en lugar de una plataforma: cada adición nueva paga el precio completo de la inconsistencia acumulada, en vez of heredar un patrón único y confiable.

El resto de este documento evalúa cada pieza de deuda con esa vara: no "¿es un bug?" sino "¿esto hace que la próxima cosa sea más cara de construir?".

---

## 2. Inventario completo de deuda técnica

Consolidado de dos rondas de recorrido de código (rutas, controladores, modelos, migraciones, frontend, configuración, CI, deploy) más verificación directa mía de los hallazgos más graves. Clasificado por severidad y por dimensión de impacto.

### 2.1 Seguridad — la categoría más grave encontrada

| Hallazgo | Ubicación | Severidad | Impacto dominante |
|---|---|---|---|
| ~~**Falsificación de confirmación de pago PayU**~~ — **RESUELTO 2026-07-07** (tarea `fix_payu_signature_verification`, `tasks.json` id 26; ver `progress/history.md`). `updateUserStatus()` marcaba una inscripción como pagada solo verificando `polTransactionState == '4'` en el request; nunca validaba la firma (`sign`) entrante. La ruta (`POST /pagos/{id}/confirmation`) no tiene auth y está explícitamente eximida de CSRF. Fix: `App\Payments\Concerns\VerifiesPayuSignature` + rechazo 403 en `PagosController::confirmation()`. Verificado 2026-07-15: integrado en `upgradee` y suite completa en verde. | `app/Payments/PayU.php:136-145`, `app/Http/Controllers/PagosController.php:40-60`, `app/Http/Middleware/VerifyCsrfToken.php:15` (verificado por mí, código leído completo) | ~~**CRÍTICA**~~ → mitigada, pendiente de verificación por ejecución | Usuarios (fraude directo) + Seguridad + Mantenimiento (contradice el estándar de verificación de firma que sí se aplica correctamente en Stripe) |
| ~~**IDOR en lectura de perfil**~~ — **RESUELTO 2026-07-15** (tarea `fix_idor_personas_show`, id 27). `show(Persona $persona)` devolvía el objeto completo sin comparar contra el usuario autenticado. Fix: `autorizarPersonaPropia()` en `api\PersonasController` (403 si no es la propia persona); investigado antes que ningún flujo legítimo dependía de leer perfiles de terceros (mobile solo usa perfil propio; backoffice va por `ajax\PersonasController`). Regresión: `tests/Feature/api/PerfilApiTest.php`. | `app/Http/Controllers/api/PersonasController.php` | ~~**CRÍTICA**~~ → resuelta, con test de regresión | Usuarios (fuga masiva de PII) + Seguridad |
| ~~**IDOR en escritura de perfil**~~ — **RESUELTO 2026-07-15** (tarea `fix_idor_personas_update`, id 28). `update()` no verificaba ownership (vector de account takeover vía cambio de mail + reset). Mismo fix y test de regresión que el anterior (`PerfilApiTest`: el 403 deja los datos de la víctima intactos, y el flujo legítimo de edición del propio perfil sigue funcionando). | `app/Http/Controllers/api/PersonasController.php` | ~~**CRÍTICA**~~ → resuelta, con test de regresión | Usuarios + Seguridad (el más grave de los tres: no es solo lectura, es escritura de identidad ajena) |
| `ReportingController` no aplica scope por país server-side; cualquier `Persona` autenticada de la app mobile trae datasets de todos los países, hasta 50.000 filas por página | `app/Http/Controllers/api/reporting/ReportingController.php:76-95` | **ALTA** | Seguridad + Evolución futura (bloquea escalar el módulo de reporting a más países sin resolver esto antes) |
| Sin lockout ni rate limit específico en login/registro; solo el throttle global de 60 req/min por IP | `app/Http/Controllers/api/PersonasController.php:52-87`, `routes/api.php:31` | **ALTA** | Seguridad |
| Sin política de complejidad de contraseña — se acepta una contraseña de 1 carácter | `app/Http/Requests/CrearPersona.php:27` | **ALTA** | Seguridad + Usuarios |
| Dependencias sin soporte desde 2020-2023: Laravel 5.7.29, Passport 7.5.1, Symfony 4.4.x, Spatie Permission 2.38 | `composer.lock` | **ALTA** | Seguridad + Evolución futura (cualquier CVE nuevo en estas líneas no tiene parche oficial) |
| `AuthenticateSession` deshabilitado — cambiar contraseña no invalida sesiones web activas en otros dispositivos | `app/Http/Kernel.php:34` | **MEDIA** | Seguridad |
| Cookies de sesión sin `secure` forzado y sin `SameSite` | `config/session.php:167,195` | **MEDIA** | Seguridad |
| Logging de la request HTTP completa de PayU en texto plano (PII de pago sin redactar) | `app/Http/Controllers/PagosController.php:54,58` | **MEDIA** | Seguridad + Auditoría |
| Mass assignment real explotable en `EvaluacionActividad::create($request->all())` | `app/EvaluacionActividad.php:11`, `app/Http/Controllers/EvaluacionesController.php:131` | **MEDIA** | Mantenimiento (impacto bajo hoy, tabla sin campos sensibles) |
| `$guarded` mínimo en `Actividad`/`Inscripcion` — mass assignment latente si un futuro endpoint usa `create($request->all())` | `app/Actividad.php:15`, `app/Inscripcion.php:16` | **MEDIA** | Evolución futura |
| Wildcard de exclusión CSRF `/pagos/*` | `app/Http/Middleware/VerifyCsrfToken.php:15` | **BAJA** | Seguridad |

### 2.2 Testing, CI/CD, observabilidad y deployment

| Hallazgo | Ubicación | Severidad | Impacto dominante |
|---|---|---|---|
| **No existe ningún pipeline de CI que ejecute los tests de forma confiable.** `.travis.yml` no tiene `script:` explícito, las migraciones están comentadas, y el `DB_HOST=mysql` que fuerza `phpunit.xml` no resuelve fuera de la red de docker-compose. No hay workflows de GitHub Actions. | `.travis.yml`, `phpunit.xml:24-27`, `.github/` (sin `workflows/`) | **CRÍTICA** | Mantenimiento + Seguridad (nada impide que un PR con una regresión, o una reintroducción de uno de los bugs ya arreglados, llegue a producción sin que ningún proceso automático lo note) |
| **Cero tests sobre los flujos de cobro con dinero real**: `StripeController` (checkout web) e `InscripcionStripeController` (pago mobile) no tienen ni un solo test. | confirmado por ausencia total en `tests/` | **CRÍTICA** | Usuarios + Seguridad (exactamente los flujos donde un bug cuesta dinero real son los que menos protección tienen) |
| **Sin estrategia de backup de base de datos** en ningún lado del repo, y `deploy.sh` corre `php artisan migrate --force` contra la BD activa sin backup previo | `deploy.sh:40`, ausencia confirmada por grep exhaustivo de "backup" | **CRÍTICA** | Usuarios (pérdida de datos irrecuperable ante una migración fallida) |
| `deploy.sh` sin rollback ante fallo de migración (solo saca de mantenimiento vía `trap`, no revierte código ni esquema) | `deploy.sh` | **ALTA** | Mantenimiento + Usuarios |
| Variables críticas de Stripe (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_DONATIONS_*`) ausentes de `.env.example` | `config/services.php:34-42` vs `.env.example` | **ALTA** | Mantenimiento (onboarding roto, riesgo de pagos mal configurados en un ambiente nuevo) |
| Sin test de login estándar Passport (solo login social cubierto) | ausencia confirmada en `tests/` | **ALTA** | Seguridad |
| Sin endpoint de health-check (`/up`, `/health`) | ausencia confirmada en `routes/` | **ALTA** | Mantenimiento + Performance (deploys y monitoreo a ciegas) |
| `Log::channel` nunca usado — todo el logging de la app cae en un único archivo plano sin separación por dominio | confirmado por grep (0 resultados) | **MEDIA** | Mantenimiento (debugging de incidentes de pago mezclado con todo el resto) |
| `supervisor-worker.conf` con `numprocs=1` y cola única — un job lento bloquea notificaciones push/mail | `supervisor-worker.conf:10` | **MEDIA** | Performance + Usuarios |
| Docker sobre PHP 7.2 / node:10 / Debian Buster archivado (parcheado a mano para poder instalar paquetes) | `docker/php/DockerFile:6-9` | **MEDIA** | Seguridad (congela la stack completa en versiones EOL, coherente con el código pero no con el mundo) |
| `init.sh` con ~15 líneas de código inalcanzable después de un `exit` | `init.sh:109-125` | **BAJA** | Mantenimiento |

### 2.3 Base de datos

| Hallazgo | Ubicación | Severidad | Impacto dominante |
|---|---|---|---|
| **Integridad referencial de las relaciones núcleo depende 100% de convención de código, no de constraints de MySQL** (`Inscripcion`↔`Persona`/`Actividad`, `ficha_medicas`↔`Persona`, `Integrantes`↔`Persona`/`Equipo`, `donations`↔`Persona` — esta última con comentario explícito en la migración renunciando a la FK "por compatibilidad") | `2013_01_01_000001_create_Inscripcion_table.php`, `2022_02_15_114917_create_ficha_medicas_table.php`, `2023_03_30_142421_create_integrantes_table.php`, `2026_05_06_000001_create_donations_table.php:14-15` | **ALTA** | Mantenimiento + evolución futura — **ya materializado**: el bug de `Persona::fusionar()` que dejaba coordinadores huérfanos, arreglado el 2026-06-17, es consecuencia directa de esto |
| Columnas JSON de negocio (`ficha_medica_campos`, `roles_tags`, `tipo_inscriptos_tag`, `actividades_tags`, `config_pago`, `metadata`) se guardan sin validar su estructura interna — la mayoría con `'sometimes'`/`'nullable'` sin shape validation | `app/Http/Requests/CrearActividad.php:74-78`, `Pais.php` (sin cast ni validación de `config_pago` en ningún punto de escritura) | **ALTA** | Mantenimiento (cualquier payload malformado rompe en lectura, no en escritura — el error aparece lejos de la causa) |
| Nombre de índice duplicado como global (`idPersona`, `idPais`) en 4 tablas cada uno — ya rompió la suite de tests en SQLite (mitigado corriendo contra MySQL, pero el esquema sigue igual) | `2013_01_01_000001_create_Inscripcion_table.php:20`, `2022_02_15_114917_create_ficha_medicas_table.php:18`, `2022_12_05_123016_create_table_estudios.php:18`, `2023_03_30_142421_create_integrantes_table.php:19` (y análogo con `idPais` en 4 tablas más) | **ALTA** | Mantenimiento (ya causó una rotura real, aunque contenida) |
| 20 clases `app/Search/*Search.php`, ninguna con base común, boilerplate de filtrado duplicado íntegro en cada una; 2 pares de nombres casi idénticos con convención de sufijo inconsistente entre sí (`TiposActividadSearch`/`TiposActividadesSearch`, `LocalidadesSearch`/`LocalidadesDataSearch`) | `app/Search/` completo | **MEDIA** | Mantenimiento (alto riesgo de import equivocado, ya se demostró que el patrón se repite sin una regla de nombrado clara) |
| `tasks.json` desincronizado del trabajo real ya hecho: `test-0-baseline` sigue `pending` pese a estar completado y documentado en `progress/history.md`; `unify_estado_inscripcion` sigue `pending` pese a que `EstadoInscripcion.php` ya resolvió esa deuda | `tasks.json` vs `progress/history.md` y `app/Services/EstadoInscripcion.php` | **MEDIA** | Mantenimiento (el propio backlog de gestión de deuda tiene el mismo problema que la documentación: se desactualiza si nadie lo reconcilia contra el código) |
| Ritmo de ~50 migraciones en los últimos 6 meses sobre un esquema de 13 años con dos convenciones de nombres coexistiendo | `database/migrations/` (187 archivos) | **MEDIA** | Evolución futura (ventana de fragilidad para el upgrade de Laravel ya planeado) |

### 2.4 Arquitectura, código muerto y duplicación

| Hallazgo | Ubicación | Severidad | Impacto dominante |
|---|---|---|---|
| 20 ocurrencias de TODO/FIXME/HACK, **2 de ellas marcadas explícitamente por el propio equipo como huecos de seguridad sin resolver** ("TODO: segurizar" en descarga de template de importación y en endpoint de logs) | `routes/web.php:493,512` | **ALTA** | Seguridad (es deuda que el equipo ya identificó y decidió posponer — la señal más clara de qué priorizar primero) |
| Validación de negocio desactivada sin documentar: el chequeo que impedía borrar actividades ya finalizadas está comentado — hoy cualquier coordinador puede eliminar actividades pasadas | `app/Http/Controllers/backoffice/ActividadesController.php:411-414` | **ALTA** | Usuarios + Auditoría (pérdida de historial operativo sin que nadie lo perciba como una decisión) |
| Bug silencioso de UI: el listener de "cerrar tooltip al hacer click afuera" está registrado pero su cuerpo está comentado — parece funcionar pero no hace nada | `resources/js/components/common/personaTooltip.vue:67-74` | **MEDIA** | Usuarios (el tipo de bug que QA manual no detecta) |
| 15+ pares de nombres de clase casi idénticos coexistiendo activamente (`PersonasController` ajax/api, `CampaniaController`/`CampanasController`, `ReportController`/`ReportingController`, `UsuarioController`/`UsuariosController`, filtros `Search/filters/Oficina.php` duplicado en dos namespaces, `Events/RegistroUsuario` vs `Notifications/RegistroUsuario`, etc.) | ver inventario completo en anexo de investigación | **ALTA** | Mantenimiento (riesgo de import equivocado que compila pero rompe en runtime — ya es un patrón repetido, no un caso aislado) |
| God objects/components: `api/DonationController.php` (835 líneas), `backoffice/ajax/InscripcionesController.php` (582), `actividad.vue` (1837 líneas — el componente más grande del sistema), `config/datatables.php` (988 líneas, 4x el segundo archivo de config más grande) | ver top-15 en anexo | **ALTA** | Mantenimiento + Performance (concentran la mayor densidad de bugs futuros y son los más caros de testear) |
| Clase `MiembrosSearch.php` sin ningún uso en todo el repo y además rota (`Builder::newQuery()` estático mal invocado) | `app/Search/MiembrosSearch.php` | **MEDIA** | Mantenimiento (código muerto + bug dormido simultáneo) |
| ~74 `console.log` activos en frontend, concentrados en manejo de errores de axios copy-pasteado | `resources/js/` | **BAJA** | Mantenimiento (ruido, no filtra datos críticos) |
| 2 archivos huérfanos de scaffolding nunca registrados (`test.vue` con HTML roto, `ExampleComponent.vue`) | `resources/js/components/test.vue`, `ExampleComponent.vue` | **BAJA** | Mantenimiento |

### 2.5 Documentación

| Hallazgo | Severidad | Impacto dominante |
|---|---|---|
| `CLAUDE.md` describe como deuda pendiente la duplicación de `estadoInscripcion()`, que ya fue resuelta vía `app/Services/EstadoInscripcion.php` | **MEDIA** | Mantenimiento (documentación que envía a resolver un problema que ya no existe hace perder tiempo real) |
| Línea de IDs hardcodeados en `routes/api.php` documentada en `CLAUDE.md` en L167-190, real en L181-204 | **BAJA** | Mantenimiento |
| Dos comandos de reporting programados (`reporting:sync-person-keys`, `reporting:snapshot-lifecycle`) no figuran en la tabla de comandos de `CLAUDE.md` | **BAJA** | Mantenimiento |
| `Suscribe`/`Campaign` documentados como "coexistiendo separados, pendiente de evaluación" cuando ya están parcialmente unificados (`Suscripciones.campaign_id`) | **MEDIA** | Mantenimiento (la documentación retrasa una decisión que en los hechos ya se empezó a tomar) |

---

## 3. Patrones y causas raíz — no son 60 problemas sueltos, son 4

Si se lee el inventario completo como una lista plana, parecen decenas de issues desconectados. No lo son. Casi todo lo de arriba es síntoma de un número muy chico de decisiones o hábitos que se repiten.

**Patrón 1 — "ante un requerimiento nuevo, se crea una implementación paralela en vez de extender la existente."** Esta es la causa raíz dominante del proyecto, y explica simultáneamente la duplicación, la confusión de nombres y la inconsistencia de convenciones que en el inventario aparecen como hallazgos separados: `PagosController`(PayU) → `StripeController` (web) → `InscripcionStripeController` (mobile) → `DonationController` (donaciones) son cuatro sistemas de cobro que nunca se consolidaron entre sí. `Suscribe` → `Campaign` son dos sistemas de captación que coexisten. `ajax\PersonasController` (casi vacío) y `api\PersonasController` (con toda la lógica) son el mismo nombre para necesidades que debieron resolverse extendiendo una sola clase. `TiposActividadSearch`/`TiposActividadesSearch` y `LocalidadesSearch`/`LocalidadesDataSearch` son el mismo patrón a nivel de clases de filtrado. No son 15 decisiones distintas de 15 desarrolladores distintos — es un único hábito institucional repetido más de una docena de veces. **Atacar este patrón en la raíz (una política explícita de "extender antes que duplicar", ya escrita en `CLAUDE.md` y `AGENTS.md` pero no aplicada retroactivamente) vale más que arreglar cada duplicación una por una.**

**Patrón 2 — "el código nuevo es mejor que el código viejo, y eso es una buena noticia mal aprovechada."** Comparando por antigüedad: `DonationController`/`StripePaymentService`/`Campaign.php` (2026) tienen validación explícita, scoping de ownership correcto en todos los métodos, manejo de idempotencia, y `$fillable` acotado. `PagosController`/`api\PersonasController`/`app/User.php` (código de 2018-2019 sin tocar desde entonces) son exactamente donde viven los tres agujeros críticos de seguridad. Esto significa que el equipo actual ya sabe escribir código seguro y lo está haciendo bien en lo nuevo — el riesgo no está distribuido parejo por todo el sistema, está concentrado en las esquinas legacy que nadie revisita porque "andan". Esto cambia la estrategia: no hace falta una auditoría de seguridad de todo el codebase con la misma profundidad, hace falta **una barrida dirigida específicamente al código pre-2023 que maneja autenticación, autorización y pagos**, que es donde la probabilidad de encontrar más hallazgos como los tres críticos es más alta.

**Patrón 3 — "no hay ninguna capa estructural que fuerce el chequeo de ownership; cada controlador lo reimplementa (o no) a mano."** `DonationController` lo hace bien en los 5 métodos revisados. `PersonasController` no lo hace en ninguno de los dos métodos revisados. La diferencia no es de talento, es de que no existe un mecanismo (policy, middleware, trait) que haga estructuralmente imposible olvidarse del chequeo — depende de que cada desarrollador se acuerde. Esto es lo mismo que ya reconoce `.claude/agents/reviewer.md` como checklist manual ("¿los nuevos métodos tienen los mismos guards de autorización que el código que rodean?") — es un buen parche de proceso, pero un plan de 5-10 años necesita que esto sea estructural (un Form Request base, un trait `AuthorizesOwnPersona`, o Policies de Laravel aplicadas consistentemente), no solo un ítem de checklist que depende de que el revisor no se lo salte.

**Patrón 4 — "no hay gate automático, así que la deuda se acumula sin fricción hasta que alguien la revisa manualmente."** Ni `.travis.yml` corre tests de forma confiable, ni hay GitHub Actions, ni `deploy.sh` corre ningún test antes de desplegar. Esto es la causa raíz de por qué el resto de los patrones pudo acumularse sin que nadie los notara antes: no hay ningún punto del ciclo de vida del código donde algo automático diga "esto está mal, no avances". Es, con diferencia, **el ítem individual de mayor apalancamiento de todo este documento** (desarrollo en la sección 5).

**Módulos que concentran más deuda** (para dónde mirar primero si hay que elegir): el eje de pagos (`PagosController`, `app/Payments/`, `StripeController`, `api/InscripcionStripeController`) concentra la mayor severidad (fraude + cero tests). El eje de autenticación/perfil (`api/PersonasController`, `app/User.php`, `RegisterController`) concentra los dos IDOR y la falta de política de contraseñas. El backoffice (53 controladores, `actividad.vue` de 1837 líneas, `config/datatables.php` de 988) concentra el mayor volumen de líneas y por lo tanto la mayor probabilidad estadística de bugs futuros, aunque no es donde está la mayor severidad de seguridad.

---

## 4. ¿Vale la pena una etapa dedicada de saneamiento? Sí — pero acotada, no indefinida

El retorno es medible con lo que ya pasó en este mismo repo: dos sesiones de trabajo enfocadas en tests (no en features) revelaron y corrigieron 6 bugs reales de producción que llevaban tiempo sin detectarse. Ese es el ROI concreto de invertir en fundamentos: no es un gasto que "no se ve", es la única forma que tiene este equipo de encontrar bugs que ya existen y que las features nuevas no van a exponer por sí solas (nadie va a notar el bug de `Persona::fusionar()` escribiendo una feature nueva de campañas).

Pero la pregunta correcta no es "¿saneamiento sí o no?", es "¿cuánto, y qué corre en paralelo con qué?". Mi recomendación, y aquí me aparto de la lectura más literal de tu pedido: no recomiendo un freeze total de meses. Recomiendo:

- **Una intervención de emergencia de días**, no negociable, que no espera a ningún roadmap: los tres hallazgos críticos de seguridad (§2.1) se parchean ahora, en aislamiento, sin esperar a nada más. Son fixes acotados (agregar verificación de firma en PayU, agregar un chequeo de ownership en dos métodos) que no requieren rediseño.
- **Una etapa de fundamentos de 6-10 semanas** donde el foco principal es CI + cobertura de los flujos de dinero + observabilidad mínima — esto sí sugiero que sea prioritario sobre features nuevas grandes, porque es la precondición para que cualquier otra inversión (incluida cualquier feature nueva) no se pierda por regresión silenciosa. No es meses, es semanas, y es medible (tests en verde en CI real, cobertura de Stripe/PayU, health-check funcionando).
- **A partir de ahí, consolidación y features nuevas conviven**, porque el gate de CI ya existe y protege ambos frentes por igual.

Frenar in totum durante 3-6 meses tiene un costo de oportunidad que no está justificado por la evidencia: la mayoría de la deuda encontrada (Search classes duplicadas, nombres confusos, documentación desactualizada) es dolorosa pero no urgente — degrada mantenibilidad, no genera un incidente esta semana. Los 3 hallazgos que sí son urgentes se resuelven en días, no requieren detener el resto de la organización.

---

## 5. Orden correcto de las mejoras — por apalancamiento futuro, no por facilidad

Ordeno por "cuánto valor protege o desbloquea", no por costo de implementación.

**1º — Cerrar los 3 agujeros de seguridad críticos (§2.1).** Apalancamiento: evita que el resto de este plan se ejecute mientras el sistema sigue siendo fraudable hoy mismo. Costo: bajo (días). Esto no es parte del roadmap estratégico, es una interrupción de emergencia que ocurre antes de que el roadmap empiece a contar.

**2º — CI real que ejecute PHPUnit + mocha-webpack contra una BD MySQL de servicio, con gate de merge obligatorio.** Este es el ítem de mayor apalancamiento estructural de todo el documento, aunque no sea el de mayor severidad individual: es pequeño (un workflow YAML + un servicio de MySQL en el runner, reutilizando la configuración que ya existe en `phpunit.xml` y `docker-compose.yml`) y **desbloquea el valor de absolutamente todo lo que sigue**. Sin esto: cada test nuevo que se escriba en la etapa siguiente puede dejar de correrse sin que nadie lo note: cada bug de seguridad que se arregle puede reintroducirse sin alarma; cada mejora de documentación no tiene forma de mantenerse sincronizada de forma verificable. Es la razón técnica concreta de por qué "una mejora chica desbloquea muchas otras": todo el resto del plan asume que existe un lugar donde una regresión se detiene antes de llegar a producción, y hoy ese lugar no existe.

**3º — Cobertura de tests de los flujos de dinero (Stripe checkout web, Stripe mobile, y un regression-test explícito del bypass de PayU una vez parcheado) + backup/restore de base de datos probado al menos una vez.** Apalancamiento: son los dos lugares donde un bug cuesta dinero real o datos irrecuperables; sin esto, el CI del punto 2 protege todo excepto exactamente lo más caro de romper.

**4º — Integridad de datos: agregar constraints FK reales a las relaciones núcleo (`Inscripcion`, `Integrantes`, `ficha_medicas`) y validación de shape a las columnas JSON de negocio.** Apalancamiento: convierte una clase entera de bugs (datos huérfanos, JSON malformado) de "silenciosos y descubiertos tarde" a "rechazados en el momento de la escritura, con un error claro". Esto es exactamente lo que ya demostró ser necesario con el bug real de `Persona::fusionar()`.

**5º — Consolidar el patrón de "implementación paralela" (Patrón 1 de la sección 3), empezando por los pares donde la confusión ya demostró causar bugs de import**: unificar los 20 `*Search` bajo una clase base (reduce boilerplate y elimina los pares de nombres confusos de un solo cambio estructural), resolver de una vez el destino de `Suscribe` vs `Campaign` (documentar la decisión, no seguir posponiéndola), y decidir explícitamente si `PagosController`/PayU se deprecia en favor de Stripe donde sea posible (dado que ya se demostró que es la pieza con peor postura de seguridad del sistema). Apalancamiento: cada consolidación no solo arregla el caso puntual, reduce la superficie total de "lugares donde hay que acordarse de aplicar la misma regla" — es la diferencia entre mantener 4 sistemas de pago con reglas de seguridad potencialmente distintas o mantener uno.

**6º — Observabilidad de producción (canales de log separados, health-check, versión/commit expuesto, APM básico).** Apalancamiento: sin esto, cualquier incidente de las etapas anteriores (y de ahí en adelante) se diagnostica a ciegas. Deliberadamente lo pongo después de CI/tests/integridad de datos porque observability sin un gate que arregle lo que observa es solo un espejo de un problema que ya sabías que existía.

**7º — Modernización (Laravel/PHP, retiro de Vue 2 a medida que el frontend nuevo reemplaza piezas).** Este es, con diferencia, el ítem más caro y más lento del plan (`tasks.json` ya lo tiene desglosado en 6 fases con niveles de riesgo, dos de ellas marcadas `high`). Deliberadamente lo pongo al final del orden de apalancamiento, no porque no importe, sino porque **hacerlo antes que el CI real sería la peor secuencia posible**: una migración de Laravel 5.7 a 11 sin una red de tests confiable en CI es exactamente el tipo de cambio de alto riesgo que más necesita ese gate ya funcionando antes de empezar.

**Por qué documentación y nomenclatura no están en el top del orden, pese a haber sido pedidas explícitamente**: son reales, pero de bajo riesgo de incidente — una `CLAUDE.md` desactualizada hace perder tiempo, no genera fraude ni pérdida de datos. Van encadenadas naturalmente a la consolidación del punto 5 (cuando se resuelve `Suscribe` vs `Campaign`, se documenta esa resolución en el mismo cambio) en vez de ser un esfuerzo separado.

---

## 6. Pilares fundacionales — mi propia lista, no la que sugeriste

Reorganizo la lista que propusiste alrededor de las causas raíz de la sección 3, no como una checklist genérica de buenas prácticas:

1. **Perímetro de autorización estructural** — que el chequeo de ownership sea imposible de olvidar (Policies/traits aplicados de forma consistente), no un ítem de checklist manual. Cubre lo que en tu lista eran "seguridad" y "permisos" por separado, porque en este código son el mismo problema con dos caras.
2. **Red de regresión automática (CI/CD real)** — el ítem de mayor apalancamiento de la sección 5. Cubre tu "CI/CD" y buena parte de "testing".
3. **Integridad de dato garantizada por el motor, no por convención** — FKs reales + validación de shape en JSON. Es un pilar propio porque hoy no existe en absoluto, y es distinto de "testing": un test no evita un dato corrupto si nadie lo pensó al escribir el test.
4. **Un único patrón por problema** (consolidación de sistemas paralelos) — el antídoto directo al Patrón 1. Es el pilar que más se parece a "arquitectura" en tu lista original, pero lo nombro por la causa raíz específica que ataca, no en abstracto.
5. **Observabilidad accionable** — logs separados por dominio, health-checks, versión expuesta, APM. Sin esto, todos los pilares anteriores son invisibles cuando fallan en producción.
6. **Documentación verificada contra el código, no aspiracional** — con un mecanismo (aunque sea manual al principio) de reconciliación periódica, dado que ya se demostró dos veces en este mismo repo (`CLAUDE.md` y `tasks.json`) que la documentación se desactualiza solo si nadie la reconcilia activamente.
7. **Continuidad operativa** (deploy con rollback, backup/restore probado, colas con prioridad) — el pilar que hoy tiene el gap más peligroso en términos de "pérdida irreversible" (no hay backup antes de migrar en producción).

No incluyo "versionado de APIs" como pilar de esta etapa, aunque sí importa (se documentó en el informe anterior): es un prerrequisito específicamente para el sistema de soporte con IA y para la modernización de Laravel, así que lo trato como parte del pilar 4 (consolidación) cuando llegue ese momento, no como un pilar fundacional independiente hoy.

---

## 7. Roadmap estratégico por etapas

Nombro las etapas después de este segundo análisis, no antes:

### Etapa 0 — Contención de emergencia (días, no una etapa del roadmap en sí)
Parchear los 3 hallazgos críticos de seguridad. Nada de este plan empieza a contar hasta que esto está resuelto, porque cualquier otra inversión convive con fraude activo mientras tanto.

### Etapa 1 — Estabilización (6-10 semanas)
CI real con gate de merge obligatorio. Cobertura de tests de los flujos de dinero. Backup/restore probado al menos una vez de punta a punta. Health-check básico. Reconciliar `tasks.json` contra el estado real del código (marcar lo que ya está hecho, como `unify_estado_inscripcion`, y descartar lo que ya no aplica). Objetivo de la etapa: que sea seguro tocar cualquier cosa sin miedo a romper algo sin enterarse.

### Etapa 2 — Consolidación (2-4 meses, en paralelo con features nuevas de bajo riesgo)
Resolver el Patrón 1 de raíz: unificar las 20 clases Search bajo una base común, decidir y documentar el destino de `Suscribe` vs `Campaign`, decidir el destino de PayU, resolver los pares de nombres confusos de mayor riesgo. Agregar constraints FK reales y validación de shape JSON en las tablas núcleo. Sincronizar documentación como parte del mismo cambio que resuelve cada consolidación, no como tarea aparte.

### Etapa 3 — Modernización (6-12 meses, la más larga y de mayor riesgo controlado)
El camino ya bosquejado en `tasks.json` (`upgrade-fase1` a `upgrade-fase6`, Laravel 5.7→6→7→8→9→10→11, con `upgrade-3a-factories` y `upgrade-3b-laravel8` ya marcadas como riesgo alto por el propio equipo). Retiro incremental de Vue 2 a medida que el frontend nuevo reemplaza piezas del monolito. Esta etapa depende estrictamente de que la Etapa 1 esté completa — es la razón técnica concreta de por qué el orden importa: migrar un framework mayor sin CI confiable es la forma más rápida de introducir una regresión invisible a gran escala.

### Etapa 4 — Automatización operativa (en paralelo con el final de la Etapa 3)
Deploy con rollback automático, colas con prioridad real (separar mail/push/reportes de la cola `default`), observabilidad completa (canales de log por dominio, APM, alertas). Es la maduración operativa de los pilares 5 y 7, y prepara el terreno de infraestructura para la etapa siguiente.

### Etapa 5 — Inteligencia operativa
Acá es donde entra el sistema de soporte asistido por IA del informe anterior — no antes. Justificación técnica de por qué acá y no en la Etapa 1 o 2: para entonces existen las cuatro precondiciones que el informe anterior identificó como faltantes — CI real que actúa como gate automático de cualquier PR generado por un agente (Etapa 1), cobertura de tests en los flujos que un agente podría tocar por error (Etapa 1), integridad de datos que reduce la clase de bugs "dato corrupto" que un agente tendría que diagnosticar a ciegas (Etapa 2), y un único patrón por problema en vez de cuatro sistemas paralelos de pago que un agente podría confundir (Etapa 2). Construir el sistema de soporte antes de esto significa, literalmente, enseñarle a una IA a operar sobre las mismas inconsistencias que se está pidiendo eliminar.

Dicho esto, no todo lo relacionado a IA debe esperar hasta la Etapa 5: **dos piezas de bajo riesgo pueden y deben empezar ya en la Etapa 2**, porque no tocan código de producción y generan el dato que la Etapa 5 va a necesitar para no arrancar en frío: (a) la instrumentación mínima de frontend para capturar contexto de errores (§1.6 del informe anterior, sigue siendo válido tal cual), y (b) un agente de Triage de solo lectura (nivel 1, sin ejecución de código) que empiece a clasificar reportes manuales y a acumular una base de casos reales. Para cuando la Etapa 5 arranque formalmente, ya habrá 1-2 años de datos reales acumulados en vez de una base vacía.

### Etapa 6 — Evolución continua
El objetivo de esta etapa no es una lista de tareas nueva, es un cambio de hábito institucional: que la política de "extender antes que duplicar" (ya escrita en `CLAUDE.md`/`AGENTS.md`) se aplique con la misma disciplina que ya demostraron las dos sesiones de estabilización de tests de junio 2026, y que el propio sistema de inteligencia operativa de la Etapa 5 se convierta en el sensor que detecta cuándo el código empieza a desviarse otra vez de los pilares de la sección 6 — cerrando el círculo sin necesitar un "plan de estabilización" nuevo cada dos años.

---

## 8. Plan para los históricos — qué hacer con todo lo que el sistema ya arrastra

No todo lo heredado merece el mismo tratamiento. Aplico una regla de decisión concreta, no "resolver todo":

**Resolver ahora, sin esperar a ninguna etapa**: los 3 hallazgos críticos de seguridad (§2.1); los 2 TODO marcados explícitamente "segurizar" por el propio equipo (`routes/web.php:493,512`) — es deuda que ya fue identificada como riesgo por quien escribió el código, no hace falta redescubrirla.

**Incorporar al backlog de la Etapa 1/2 con criterios de aceptación explícitos, reusando `tasks.json` que ya tiene el formato correcto**: la mayoría de las tareas `pending` ya existentes (`move_hardcoded_category_ids`, `remove_dead_user_code`, la unificación de Search, la decisión Suscribe/Campaign) más las nuevas que salen de este documento (constraints FK, validación de JSON, cobertura Stripe). No hace falta inventar un sistema de gestión nuevo — el que ya existe funciona, solo hay que reconciliarlo primero.

**Reconciliar antes de usar como base de planificación**: `tasks.json` tiene al menos 2 tareas marcadas `pending` que ya están resueltas en el código (`test-0-baseline`, `unify_estado_inscripcion`) — la primera acción de la Etapa 1 debería ser una pasada de reconciliación completa de `tasks.json` contra `progress/history.md` y el código real, exactamente el mismo ejercicio que este documento hizo con `CLAUDE.md`.

**Documentar como decisión aceptada, no como deuda pendiente**: el churn de columnas ya revertidas (rol/cargo, slug de campaigns, pasaporte, visibilidad) no necesita ninguna acción — es historia ya cerrada, mencionarla en la documentación solo como contexto de por qué ciertas migraciones existen, sin tratarla como trabajo pendiente. Las 187 migraciones no deberían squashearse todavía: es prematuro y el riesgo no se justifica hasta después de completar la modernización de Laravel (Etapa 3) — squashear antes de una migración de framework mayor agrega una variable más a un cambio ya riesgoso.

**Descartar directamente**: los 2 archivos huérfanos de scaffolding (`test.vue`, `ExampleComponent.vue`), la clase `MiembrosSearch.php` (muerta y rota), los bloques de código comentado que son boilerplate de scaffolding sin valor real (los de `ProvinciasController`/`InstitucionEducativaController` documentados en el inventario). Esto no requiere una etapa, es una tarea de una tarde con bajo riesgo, ideal para hacerse en paralelo con cualquier otra cosa.

**Actualizar en el momento en que se toca, no antes**: la documentación desactualizada de `CLAUDE.md` (línea de IDs hardcodeados, comandos de reporting faltantes, estado real de Suscribe/Campaign) se corrige como parte natural de la Etapa 2 cuando se consolide cada uno de esos puntos — no vale la pena una "tarea de arreglar documentación" separada del trabajo que la vuelve correcta.

---

## 9. Honestidad final sobre dónde podrías estar priorizando mal

Me pediste que te lo dijera si veía algo así, y hay dos puntos concretos:

Primero, el riesgo de sobre-corregir hacia la parálisis: si la conclusión que sacás de este documento es "no tocamos nada más hasta que todo el inventario de la sección 2 esté en cero", vas a estar posponiendo valor real para los voluntarios y coordinadores por deuda que es real pero no urgente (la mayoría de los pares de nombres confusos, el `console.log` residual, `config/datatables.php` de 988 líneas). Ninguno de esos genera un incidente esta semana. La secuencia de la sección 5 y las etapas de la sección 7 están diseñadas explícitamente para evitar ese modo de falla: casi todo corre en paralelo con desarrollo normal una vez que el CI (Etapa 1) existe.

Segundo, un riesgo en la dirección contraria que también vale la pena decir: no confundas "arreglamos los 3 críticos y levantamos CI" con "ya estamos estables". Esos dos pasos son baratos y rápidos, pero no tocan la causa raíz más profunda (Patrón 1, la duplicación sistemática) ni la modernización del framework, que son trabajo de meses reales, no de semanas. Si la organización espera que "la fase de estabilización" termine en 6-10 semanas en el sentido completo (incluida la Etapa 3 de modernización), va a haber una desilusión — la Etapa 1 hace que sea seguro seguir construyendo, no hace que el sistema ya esté modernizado. Son logros distintos, y comunicarlos como si fueran el mismo hito sería el error de expectativas más probable de este plan.

---

## Respuesta directa a la pregunta que planteaste

¿Cómo transformar este proyecto en una plataforma sólida, mantenible, preparada para incorporar agentes de IA y capaz de evolucionar sin volver a acumular deuda? Cerrando primero los tres agujeros de seguridad activos (días), levantando un gate de CI real que haga estructuralmente imposible que una regresión pase desapercibida (semanas), garantizando que los flujos de dinero y los datos núcleo tengan la protección de tests e integridad referencial que hoy no tienen (semanas), consolidando los sistemas paralelos que son la causa raíz de la mayor parte de la confusión del código (meses, en paralelo con desarrollo normal), modernizando el framework solo una vez que ese gate ya protege el proceso (meses, siguiendo el plan que `tasks.json` ya empezó a bosquejar), y recién ahí — con datos ya acumulados desde antes gracias a instrumentación temprana de bajo riesgo — construyendo el sistema de inteligencia operativa sobre una base que, para ese momento, será consistente y no desordenada. No se trata de elegir entre estabilidad y velocidad: se trata de invertir unas pocas semanas en la pieza (CI) que hace que ambas dejen de estar en tensión.
