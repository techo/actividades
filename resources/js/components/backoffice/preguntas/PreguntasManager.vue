<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" :id="modalId" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">{{ editando ? $t('backend.edit') : $t('backend.create') }} {{ $t('backend.pregunta') }}</h4>
                    </div>
                    <div class="modal-body">

                        <div v-if="errorGeneral" class="callout callout-danger">
                            <p>{{ errorGeneral }}</p>
                        </div>

                        <!-- Pregunta -->
                        <div :class="{ 'form-group': true, 'has-error': errors.pregunta }">
                            <label>{{ $t('backend.pregunta') }} <span class="text-danger">*</span></label>
                            <input v-model="form.pregunta" type="text" class="form-control" maxlength="500">
                            <span v-if="errors.pregunta" class="help-block">{{ errors.pregunta[0] }}</span>
                        </div>

                        <!-- Descripción -->
                        <div :class="{ 'form-group': true, 'has-error': errors.descripcion }">
                            <label>{{ $t('backend.descripcion_ayuda') }}</label>
                            <input v-model="form.descripcion" type="text" class="form-control" maxlength="1000">
                            <span v-if="errors.descripcion" class="help-block">{{ errors.descripcion[0] }}</span>
                        </div>

                        <!-- Tipo + Requerida -->
                        <div class="row">
                            <div class="col-sm-7">
                                <div :class="{ 'form-group': true, 'has-error': errors.tipo }">
                                    <label>{{ $t('backend.tipo') }}</label>
                                    <select v-model="form.tipo" class="form-control">
                                        <option value="abierta">{{ $t('backend.tipo_abierta') }}</option>
                                        <option value="desplegable">{{ $t('backend.tipo_desplegable') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <label class="d-block">{{ $t('backend.requerida') }}</label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" v-model="form.requerida">
                                        {{ $t('backend.hacer_obligatoria') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones (solo desplegable) — editor que preserva el id estable -->
                        <div v-if="form.tipo === 'desplegable'" :class="{ 'form-group': true, 'has-error': errors.opciones }">
                            <label>{{ $t('backend.opciones_disponibles') }}</label>
                            <div v-for="(opcion, idx) in form.opciones" :key="opcion.id || ('new-' + idx)" class="input-group mb-1">
                                <input v-model="opcion.label" type="text" class="form-control" :placeholder="$t('backend.opcion')">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" @click="quitarOpcion(idx)">
                                        <i class="fa fa-times text-danger"></i>
                                    </button>
                                </span>
                            </div>
                            <button class="btn btn-sm btn-default" type="button" @click="agregarOpcion()">
                                <i class="fa fa-plus"></i> {{ $t('backend.agregar_opcion') }}
                            </button>
                            <span v-if="errors.opciones" class="help-block">{{ errors.opciones[0] }}</span>
                        </div>

                        <hr>

                        <!-- Filtro Condicional -->
                        <div class="form-group">
                            <label>{{ $t('backend.filtro_condicional') }}</label>
                            <p class="help-block text-muted" style="margin-top:0">
                                {{ $t('backend.filtro_condicional_ayuda') }}
                            </p>

                            <div v-if="!form.condicionActiva">
                                <button class="btn btn-sm btn-info" type="button"
                                        :disabled="preguntasPadrePosibles.length === 0"
                                        @click="activarCondicion()">
                                    <i class="fa fa-plus"></i> {{ $t('backend.filtro_especifico') }}
                                </button>
                                <span v-if="preguntasPadrePosibles.length === 0" class="help-block text-muted">
                                    {{ $t('backend.filtro_sin_preguntas') }}
                                </span>
                            </div>

                            <div v-else class="well" style="padding:12px">
                                <p style="margin-bottom:8px"><strong>{{ $t('backend.mostrar_si') }}</strong></p>
                                <div class="form-group">
                                    <label>{{ $t('backend.pregunta_padre') }}</label>
                                    <select v-model="form.condicion.parent_id" class="form-control" @change="form.condicion.value = ''">
                                        <option :value="null">—</option>
                                        <option v-for="p in preguntasPadrePosibles" :key="p.id" :value="p.id">
                                            {{ p.pregunta }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{ $t('backend.respuesta_esperada') }}</label>
                                    <select v-model="form.condicion.value" class="form-control" :disabled="!form.condicion.parent_id">
                                        <option value="">—</option>
                                        <option v-for="op in opcionesDelPadre" :key="op.id" :value="op.id">
                                            {{ op.label }}
                                        </option>
                                    </select>
                                </div>
                                <button class="btn btn-xs btn-link text-danger" type="button" @click="quitarCondicion()">
                                    {{ $t('backend.eliminar_configuracion') }}
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn" @click="cancelar()">{{ $t('backend.cancel') }}</button>
                        <button v-if="editando" class="btn btn-danger" @click.prevent="confirmarEliminar()">{{ $t('backend.eliminate') }}</button>
                        <button class="btn btn-primary" @click.prevent="guardar()" :disabled="guardando">
                            {{ guardando ? $t('backend.saving') : $t('backend.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="pull-right">
                            <button class="btn btn-primary" @click.prevent="abrirModalCrear()">
                                {{ $t('backend.create') }} <i class="fa fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <br>

                <div v-if="cargando" class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> {{ $t('backend.loading') }}
                </div>

                <div v-else-if="preguntas.length === 0" class="text-muted text-center">
                    {{ $t('backend.no_preguntas') }}
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ $t('backend.orden') }}</th>
                                <th>{{ $t('backend.pregunta') }}</th>
                                <th>{{ $t('backend.tipo') }}</th>
                                <th>{{ $t('backend.requerida') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pregunta in preguntas" :key="pregunta.id">
                                <td>{{ pregunta.orden }}</td>
                                <td>
                                    {{ pregunta.pregunta }}
                                    <small v-if="pregunta.descripcion" class="text-muted d-block">{{ pregunta.descripcion }}</small>
                                    <small v-if="condicionDe(pregunta)" class="text-info d-block">
                                        <i class="fa fa-filter"></i>
                                        {{ resumenCondicion(pregunta) }}
                                    </small>
                                </td>
                                <td>{{ pregunta.tipo === 'desplegable' ? $t('backend.tipo_desplegable') : $t('backend.tipo_abierta') }}</td>
                                <td>
                                    <i :class="pregunta.requerida ? 'fa fa-check text-success' : 'fa fa-times text-muted'"></i>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-default" @click.prevent="abrirModalEditar(pregunta)">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
/**
 * Gestor unificado de preguntas configurables (Actividades y Campañas).
 * Reemplaza a preguntas-actividad.vue y CampanaPreguntas.vue, que eran calcados.
 * Parametrizado por `baseUrl` (el endpoint REST de preguntas de la entidad).
 *
 * Soporta:
 *   - Editor de opciones con id estable (se preserva al editar).
 *   - Filtro condicional: mostrar la pregunta solo si otra pregunta anterior
 *     tiene una respuesta específica (comparada por id de opción, no por texto).
 */
export default {
    name: 'preguntas-manager',
    props: {
        baseUrl: {
            type: String,
            required: true,
        },
        modalId: {
            type: String,
            default: 'preguntas-manager-modal',
        },
    },
    data() {
        return {
            preguntas: [],
            cargando: false,
            guardando: false,
            errorGeneral: null,
            errors: {},
            form: this.formVacio(),
        };
    },
    computed: {
        editando() {
            return !!this.form.id;
        },
        // Candidatas a pregunta padre: desplegables ANTERIORES (orden menor), no la actual.
        preguntasPadrePosibles() {
            const ordenActual = this.form.orden != null ? this.form.orden : 0;
            const idActual = this.form.id;
            return this.preguntas.filter((p) => {
                return p.id !== idActual
                    && p.tipo === 'desplegable'
                    && p.orden < ordenActual;
            });
        },
        opcionesDelPadre() {
            const pid = this.form.condicion ? this.form.condicion.parent_id : null;
            if (!pid) return [];
            const padre = this.preguntas.find((p) => p.id === pid);
            return padre && Array.isArray(padre.opciones_detalle) ? padre.opciones_detalle : [];
        },
    },
    mounted() {
        this.cargarPreguntas();
    },
    methods: {
        formVacio() {
            return {
                id: null,
                pregunta: '',
                descripcion: '',
                tipo: 'abierta',
                opciones: [],
                requerida: false,
                orden: 0,
                condicionActiva: false,
                condicion: { parent_id: null, operator: 'equals', value: '' },
            };
        },
        cargarPreguntas() {
            this.cargando = true;
            axios.get(this.baseUrl)
                .then((res) => { this.preguntas = res.data; })
                .catch(() => {})
                .finally(() => { this.cargando = false; });
        },
        condicionDe(pregunta) {
            return pregunta.condiciones && pregunta.condiciones.length ? pregunta.condiciones[0] : null;
        },
        resumenCondicion(pregunta) {
            const cond = this.condicionDe(pregunta);
            if (!cond) return '';
            const padre = this.preguntas.find((p) => p.id === cond.parent_id);
            const padreTxt = padre ? padre.pregunta : '#' + cond.parent_id;
            let valorTxt = cond.value;
            if (padre && Array.isArray(padre.opciones_detalle)) {
                const op = padre.opciones_detalle.find((o) => String(o.id) === String(cond.value));
                if (op) valorTxt = op.label;
            }
            return this.$t('backend.mostrar_si') + ' "' + padreTxt + '" = "' + valorTxt + '"';
        },
        abrirModalCrear() {
            this.form = this.formVacio();
            this.errors = {};
            this.errorGeneral = null;
            $('#' + this.modalId).modal('show');
        },
        abrirModalEditar(pregunta) {
            const cond = (pregunta.condiciones && pregunta.condiciones.length) ? pregunta.condiciones[0] : null;
            this.form = {
                id: pregunta.id,
                pregunta: pregunta.pregunta,
                descripcion: pregunta.descripcion || '',
                tipo: pregunta.tipo,
                // Copia de opciones_detalle preservando ids existentes.
                opciones: Array.isArray(pregunta.opciones_detalle)
                    ? pregunta.opciones_detalle.map((o) => ({ id: o.id, label: o.label }))
                    : [],
                requerida: !!pregunta.requerida,
                orden: pregunta.orden,
                condicionActiva: !!cond,
                condicion: cond
                    ? { parent_id: cond.parent_id, operator: cond.operator || 'equals', value: cond.value }
                    : { parent_id: null, operator: 'equals', value: '' },
            };
            this.errors = {};
            this.errorGeneral = null;
            $('#' + this.modalId).modal('show');
        },
        cancelar() {
            $('#' + this.modalId).modal('hide');
            this.form = this.formVacio();
            this.errors = {};
            this.errorGeneral = null;
        },
        agregarOpcion() {
            this.form.opciones.push({ id: null, label: '' });
        },
        quitarOpcion(idx) {
            this.form.opciones.splice(idx, 1);
        },
        activarCondicion() {
            this.form.condicionActiva = true;
            this.form.condicion = { parent_id: null, operator: 'equals', value: '' };
        },
        quitarCondicion() {
            this.form.condicionActiva = false;
            this.form.condicion = { parent_id: null, operator: 'equals', value: '' };
        },
        guardar() {
            if (this.editando) {
                this.actualizar();
            } else {
                this.crear();
            }
        },
        payload() {
            const esDesplegable = this.form.tipo === 'desplegable';

            // Opciones: enviar [{id,label}] (id null para nuevas → el backend lo asigna).
            const opciones = esDesplegable
                ? this.form.opciones
                    .filter((o) => o.label && o.label.trim() !== '')
                    .map((o) => ({ id: o.id || null, label: o.label.trim() }))
                : [];

            // Condición: solo si está activa y completa.
            let condicion = null;
            if (this.form.condicionActiva
                && this.form.condicion.parent_id
                && this.form.condicion.value) {
                condicion = {
                    parent_id: this.form.condicion.parent_id,
                    operator: this.form.condicion.operator || 'equals',
                    value: String(this.form.condicion.value),
                };
            }

            return {
                pregunta: this.form.pregunta,
                descripcion: this.form.descripcion || null,
                tipo: this.form.tipo,
                opciones: opciones,
                requerida: this.form.requerida,
                orden: this.form.orden,
                condicion: condicion,
            };
        },
        crear() {
            this.guardando = true;
            this.errorGeneral = null;
            axios.post(this.baseUrl, this.payload())
                .then(() => { this.cargarPreguntas(); this.cancelar(); })
                .catch((error) => { this.manejarError(error); })
                .finally(() => { this.guardando = false; });
        },
        actualizar() {
            this.guardando = true;
            this.errorGeneral = null;
            axios.put(this.baseUrl + '/' + this.form.id, this.payload())
                .then(() => { this.cargarPreguntas(); this.cancelar(); })
                .catch((error) => { this.manejarError(error); })
                .finally(() => { this.guardando = false; });
        },
        confirmarEliminar() {
            if (!confirm(this.$t('backend.confirm_delete'))) return;
            this.eliminar();
        },
        eliminar() {
            axios.delete(this.baseUrl + '/' + this.form.id)
                .then(() => { this.cargarPreguntas(); this.cancelar(); })
                .catch(() => { this.errorGeneral = this.$t('backend.error_guardando'); });
        },
        manejarError(error) {
            if (error.response && error.response.data && error.response.data.errors) {
                this.errors = error.response.data.errors;
            } else {
                this.errorGeneral = this.$t('backend.error_guardando');
            }
        },
    },
};
</script>

<style scoped>
</style>
