# AGENTS.md — Mapa de navegación para agentes de IA

> Punto de entrada para cualquier agente que trabaje en este repositorio.
> Es un **mapa**, no una biblia de reglas. Leer solo lo que se necesita cuando se necesita.
> El contexto técnico profundo está en `CLAUDE.md` — contiene las trampas del sistema y debe leerse antes de cualquier trabajo.

---

## 1. Antes de empezar (obligatorio)

1. Lee `CLAUDE.md` — contiene las trampas críticas del sistema (auth, naming de tablas, efectos secundarios).
2. Lee `progress/current.md` — estado de la última sesión.
3. Lee `tasks.json` — elige **una** tarea con `status: "pending"`. No trabajar en más de una a la vez.
4. Antes de modificar cualquier cosa, entendé cómo se usa el archivo en el sistema.

---

## 2. Mapa del repositorio

| Archivo / carpeta | Qué contiene | Cuándo leerlo |
|---|---|---|
| `CLAUDE.md` | Mapa técnico del proyecto: modelos, auth, convenciones, deuda técnica | **Siempre, antes de cualquier trabajo** |
| `tasks.json` | Lista de tareas con estado (pending / in_progress / done) | Siempre, al empezar |
| `progress/current.md` | Estado de la sesión actual | Siempre, al empezar |
| `progress/history.md` | Bitácora append-only de sesiones anteriores | Si necesitás contexto histórico |
| `docs/api.md` | Referencia completa de la API mobile | Antes de modificar rutas o controladores `api/` |
| `docs/push-notifications.md` | Arquitectura de notificaciones push — el doc más completo | Antes de tocar jobs o push |
| `app/Services/InscripcionFlow.php` | Fuente de verdad del flujo de inscripción (con comentarios) | Antes de modificar inscripciones |
| `routes/api.php` | Rutas de la API mobile | Para entender endpoints expuestos |
| `routes/web.php` | Rutas web (backoffice + frontend público) | Para entender rutas web |
| `app/Http/Controllers/api/` | Controladores consumidos por la app mobile | Para modificar la API |
| `app/Http/Controllers/backoffice/` | Controladores del backoffice web | Para modificar el backoffice |
| `app/Search/` | Patrón Search Objects para filtros de listados | Antes de agregar filtros |
| `config/auth.php` | Confirma que el modelo de auth es `App\Persona`, no `App\User` | Si hay dudas de autenticación |

---

## 3. Reglas duras

* **Una sola tarea a la vez.** No mezclar cambios de varias tareas en la misma sesión.
* **No modificar comportamiento existente sin explicar el impacto.** Este es un sistema en producción con usuarios activos.
* **Backward compatibility siempre.** Si un cambio afecta una API, evaluar el impacto en el frontend existente y la app mobile.
* **Preferir extender antes que cambiar.** Nuevos parámetros opcionales > cambiar contratos existentes.
* **No eliminar código sin verificar dependencias** (grep exhaustivo antes de borrar).
* **Si algo no está claro, preguntar antes de modificar.**

---

## 4. Cómo elegir y tomar una tarea

```
1. Abrir tasks.json
2. Filtrar por status == "pending"
3. Tomar la de menor "id"
4. Cambiar su status a "in_progress" y guardar
5. Anotar en progress/current.md: tarea, hora de inicio, plan breve
```

---

## 5. Roles de agente

Ver `.claude/agents/` para las definiciones completas de cada rol:

- **`leader.md`** — decide qué tarea tomar, crea el plan en `progress/current.md`, no toca código
- **`implementer.md`** — ejecuta los cambios, carga CLAUDE.md + archivos relevantes de la tarea
- **`reviewer.md`** — valida backward compat y riesgos de producción, lee el diff + reglas de API

Cuando trabajás solo, podés invocar cada rol explícitamente: "actuá como implementador de la tarea X" / "ahora revisá como reviewer".

---

## 6. Cierre de sesión

Antes de terminar:

1. Ejecutar `./init.sh` — debe terminar sin errores.
2. Si la tarea está terminada: marcar `status: "done"` en `tasks.json` con `completed_at`.
3. Mover el resumen de `progress/current.md` al final de `progress/history.md`.
4. Vaciar `progress/current.md` dejando solo la plantilla.
5. No dejar `dd()`, `dump()` ni `var_dump()` en el código.

---

## 7. Si te bloqueás

* Releer `CLAUDE.md` y `docs/` antes de inventar un workaround.
* Si el bloqueo es real: documentarlo en `progress/current.md` con contexto y parar la sesión.
* No forzar cambios que rompan el sistema por salir del paso.
