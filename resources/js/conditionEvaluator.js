/**
 * Espejo en cliente de App\Services\Preguntas\ConditionEvaluator (PHP).
 * Misma lógica: una pregunta es visible si no tiene condiciones o si todas se
 * cumplen. La comparación se hace por id estable de opción, no por texto.
 *
 * Mantener sincronizado con el servicio PHP ante cualquier cambio de reglas.
 */

function opcionesDetalle(pregunta) {
    if (!pregunta) return [];
    if (Array.isArray(pregunta.opciones_detalle)) return pregunta.opciones_detalle;
    return [];
}

function condicionesDe(pregunta) {
    if (!pregunta) return [];
    if (Array.isArray(pregunta.condiciones)) return pregunta.condiciones;
    return [];
}

/**
 * Dado el texto de respuesta elegido, devuelve el id estable de la opción.
 */
export function opcionIdSeleccionada(pregunta, respuestaTexto) {
    if (respuestaTexto === null || respuestaTexto === undefined || respuestaTexto === '') {
        return null;
    }
    var opciones = opcionesDetalle(pregunta);
    for (var i = 0; i < opciones.length; i++) {
        if (String(opciones[i].label) === String(respuestaTexto)) {
            return opciones[i].id != null ? opciones[i].id : null;
        }
    }
    return null;
}

function condicionSeCumple(cond, byId, respuestas, cache) {
    var parentId = cond.parent_id;
    var operator = cond.operator || 'equals';
    var esperado = cond.value;

    // Padre inexistente → fail-open.
    if (!parentId || !byId[parentId]) return true;

    // Cascada.
    if (!esVisiblePorId(parentId, byId, respuestas, cache)) return false;

    var respuestaPadre = respuestas[parentId];
    var opcionId = opcionIdSeleccionada(byId[parentId], respuestaPadre);

    switch (operator) {
        case 'equals':
        default:
            return opcionId !== null && String(opcionId) === String(esperado);
    }
}

function esVisiblePorId(id, byId, respuestas, cache) {
    if (Object.prototype.hasOwnProperty.call(cache, id)) return cache[id];
    cache[id] = true; // guard anti-ciclos

    var pregunta = byId[id];
    if (!pregunta) { cache[id] = true; return true; }

    var condiciones = condicionesDe(pregunta);
    if (!condiciones.length) { cache[id] = true; return true; }

    for (var i = 0; i < condiciones.length; i++) {
        if (!condicionSeCumple(condiciones[i], byId, respuestas, cache)) {
            cache[id] = false;
            return false;
        }
    }
    cache[id] = true;
    return true;
}

function indexar(preguntas) {
    var byId = {};
    (preguntas || []).forEach(function (p) {
        if (p && p.id != null) byId[p.id] = p;
    });
    return byId;
}

/**
 * @param {Array}  preguntas  preguntas (con condiciones y opciones_detalle)
 * @param {Object} respuestas { pregunta_id: textoRespuesta }
 * @param {Object} pregunta   la pregunta a evaluar
 * @returns {boolean}
 */
export function esVisible(pregunta, preguntas, respuestas) {
    if (!pregunta) return true;
    var byId = indexar(preguntas);
    return esVisiblePorId(pregunta.id, byId, respuestas || {}, {});
}

/**
 * @returns {Array} ids de preguntas visibles
 */
export function visibleIds(preguntas, respuestas) {
    var byId = indexar(preguntas);
    var cache = {};
    var visibles = [];
    Object.keys(byId).forEach(function (id) {
        if (esVisiblePorId(byId[id].id, byId, respuestas || {}, cache)) {
            visibles.push(byId[id].id);
        }
    });
    return visibles;
}

export default { esVisible: esVisible, visibleIds: visibleIds, opcionIdSeleccionada: opcionIdSeleccionada };
