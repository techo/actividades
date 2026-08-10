<template>
    <div class="filtros-listado">
        <!-- Barra compacta pegada a la tabla: botón para desplegar el
             constructor + chips de las condiciones activas + total. -->
        <div class="filtros-barra">
            <button type="button"
                    class="btn btn-default btn-filtros"
                    :class="{ 'btn-filtros--activo': builderAbierto || condiciones.length }"
                    @click.prevent="toggleBuilder">
                <i class="fa fa-sliders"></i>
                {{ $t('backend.advanced_search') }}
                <span v-if="condiciones.length" class="filtros-contador">{{ condiciones.length }}</span>
                <i class="fa" :class="builderAbierto ? 'fa-angle-up' : 'fa-angle-down'"></i>
            </button>

            <template v-if="condiciones.length">
                <chip v-for="(c, index) in condiciones"
                      :key="c.id"
                      :index="index"
                      :valor="c.campoLabel + ' ' + opLabel(c.condicion) + ' ' + valorLabel(c)">
                </chip>
                <a href="#" class="limpiar-todo" @click.prevent="limpiarTodo">
                    {{ $t('backend.clear_all') }}
                </a>
            </template>

            <span class="pull-right text-info total-coincidencias" v-if="total !== null">
                <strong>{{ total }}</strong> {{ $t('backend.records') }}
            </span>
        </div>

        <transition name="filtros-desplegar">
            <div class="filtros-builder" v-show="builderAbierto">
                <div class="panel panel-default" style="padding: 12px; margin-bottom: 10px;">
                    <p class="text-muted" style="margin-bottom: 8px;">
                        {{ $t('backend.build_condition') }}
                        <small>campo · operador · valor</small>
                    </p>
                    <div class="form-inline">
                        <select class="form-control" v-model="campoKey" style="min-width: 12em;">
                            <option value="">{{ $t('backend.field') }}...</option>
                            <optgroup v-for="g in filtrablesPorGrupo" :key="g.key" :label="grupoLabel(g)">
                                <option v-for="f in g.campos" :key="f.key" :value="f.key">{{ label(f) }}</option>
                            </optgroup>
                        </select>

                        <select class="form-control" v-model="operador" v-if="campo" style="min-width: 9em;">
                            <option v-for="op in campo.operadores" :key="op" :value="op">{{ opLabel(op) }}</option>
                        </select>

                        <select class="form-control" v-if="opcionesDelCampo" v-model="valor" style="min-width: 10em;">
                            <option value="">{{ $t('backend.choose') }}...</option>
                            <option v-for="o in opcionesDelCampo" :key="o" :value="o">{{ o }}</option>
                        </select>
                        <select class="form-control" v-else-if="campo && campo.opciones_remotas" disabled style="min-width: 10em;">
                            <option>{{ $t('backend.loading') }}...</option>
                        </select>
                        <select class="form-control" v-else-if="campo && campo.type === 'bool'" v-model="valor" style="min-width: 8em;">
                            <option value="1">{{ $t('backend.yes') }}</option>
                            <option value="0">{{ $t('backend.no') }}</option>
                        </select>
                        <input class="form-control" v-else-if="campo" v-model="valor"
                               :type="campo.type === 'date' ? 'date' : (campo.type === 'number' ? 'number' : 'text')"
                               :placeholder="$t('backend.value')" style="min-width: 10em;">

                        <button class="btn btn-dark btn-default" :disabled="!puedeAgregar" @click.prevent="agregar">
                            {{ $t('backend.add_condition') }}
                        </button>
                    </div>
                    <p v-if="campo" class="text-muted" style="margin-top: 8px; margin-bottom: 0;">
                        {{ $t('backend.matches_for_condition') }}:
                        <strong>{{ preview === null ? '—' : preview }}</strong>
                    </p>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    import chip from '../../plugins/chip';

    /**
     * Constructor de filtros genérico para los listados configurables.
     * Se alimenta de /admin/ajax/listados/{listKey}/{contextId}/config (campos
     * `filtrables`) y consulta /count para el preview y el total. Emite el
     * evento `filtros:cambio:{listKey}` con el array de condiciones aplicadas;
     * la datatable lo escucha para re-consultar.
     */
    export default {
        name: 'filtros-listado',
        components: { chip },
        props: {
            listKey: { type: String, required: true },
            contextId: { required: true },
            abierto: { type: Boolean, default: false },
        },
        data() {
            return {
                builderAbierto: this.abierto,
                filtrables: [],
                condiciones: [],
                campoKey: '',
                operador: '',
                valor: '',
                preview: null,
                total: null,
                opcionesCache: {},
                _seq: 0,
                _previewTimer: null,
            };
        },
        computed: {
            baseUrl() {
                return `/admin/ajax/listados/${this.listKey}/${this.contextId}`;
            },
            campo() {
                return this.filtrables.find(f => f.key === this.campoKey) || null;
            },
            // Agrupa los campos filtrables por categoría del catálogo, en el mismo
            // orden en que aparecen (igual que el panel de columnas).
            filtrablesPorGrupo() {
                const grupos = [];
                const index = {};
                this.filtrables.forEach(f => {
                    const key = f.grupo || 'otros';
                    if (index[key] === undefined) {
                        index[key] = grupos.length;
                        grupos.push({ key, label: f.grupo_label, campos: [] });
                    }
                    grupos[index[key]].campos.push(f);
                });
                return grupos;
            },
            puedeAgregar() {
                return this.campo && this.operador !== '' && this.valor !== '';
            },
            // Opciones del <select> de valor: estáticas del campo (estado/etiquetas/
            // desplegable) o las traídas on-demand para campos de dominio finito.
            opcionesDelCampo() {
                if (!this.campo) return null;
                if (this.campo.opciones && this.campo.opciones.length) return this.campo.opciones;
                if (this.campo.opciones_remotas) return this.opcionesCache[this.campo.key] || null;
                return null;
            },
        },
        watch: {
            campoKey() {
                this.operador = this.campo && this.campo.operadores.length ? this.campo.operadores[0] : '';
                this.valor = '';
                this.preview = null;
                this.cargarOpcionesRemotas();
            },
            operador() { this.recalcularPreview(); },
            valor() { this.recalcularPreview(); },
        },
        created() {
            axios.get(`${this.baseUrl}/config`).then(({ data }) => {
                this.filtrables = data.filtrables || [];
            });
            this.recalcularTotal();
        },
        methods: {
            toggleBuilder() {
                this.builderAbierto = !this.builderAbierto;
            },
            label(f) {
                return f.label && f.label.includes('.') ? this.$t(f.label) : f.label;
            },
            grupoLabel(g) {
                return g.label && g.label.includes('.') ? this.$t(g.label) : g.label;
            },
            labelDeCampo(key) {
                const f = this.filtrables.find(x => x.key === key);
                return f ? this.label(f) : key;
            },
            // Trae las opciones del campo elegido solo si tiene dominio finito y no
            // están cacheadas (lazy: no se cargan todas al abrir la página).
            cargarOpcionesRemotas() {
                const campo = this.campo;
                if (!campo || !campo.opciones_remotas) return;
                if (this.opcionesCache[campo.key]) return;
                axios.get(`${this.baseUrl}/opciones`, { params: { campo: campo.key } })
                    .then(({ data }) => {
                        this.$set(this.opcionesCache, campo.key, data.opciones || []);
                    });
            },
            opLabel(op) {
                const labels = {
                    like: 'contiene', '=': 'es', '<>': 'no es', in: 'está en',
                    '<': '<', '<=': '≤', '>': '>', '>=': '≥', between: 'entre',
                    contains: 'incluye', not_contains: 'no incluye',
                };
                return labels[op] || op;
            },
            valorLabel(c) {
                if (c.valor === '1' || c.valor === 1) return this.$t('backend.yes');
                if (c.valor === '0' || c.valor === 0) return this.$t('backend.no');
                return c.valor;
            },
            condicionActual() {
                return { campo: this.campoKey, condicion: this.operador, valor: this.valor };
            },
            recalcularPreview() {
                if (!this.puedeAgregar) { this.preview = null; return; }
                clearTimeout(this._previewTimer);
                this._previewTimer = setTimeout(() => {
                    const seq = ++this._seq;
                    axios.get(`${this.baseUrl}/count`, {
                        params: { condiciones: this.condicionesParam(), preview: JSON.stringify(this.condicionActual()) },
                    }).then(({ data }) => {
                        if (seq === this._seq) this.preview = data.preview;
                    });
                }, 300);
            },
            recalcularTotal() {
                axios.get(`${this.baseUrl}/count`, { params: { condiciones: this.condicionesParam() } })
                    .then(({ data }) => { this.total = data.total; });
            },
            condicionesParam() {
                return this.condiciones.map(c => JSON.stringify({ campo: c.campo, condicion: c.condicion, valor: c.valor }));
            },
            agregar() {
                if (!this.puedeAgregar) return;
                this.condiciones.push({
                    id: Date.now() + '_' + this.condiciones.length,
                    campo: this.campoKey,
                    campoLabel: this.label(this.campo),
                    condicion: this.operador,
                    valor: this.valor,
                });
                this.campoKey = '';
                this.operador = '';
                this.valor = '';
                this.preview = null;
                this.emitirCambio();
            },
            limpiarTodo() {
                this.condiciones = [];
                this.emitirCambio();
            },
            emitirCambio() {
                const payload = this.condiciones.map(c => ({ campo: c.campo, condicion: c.condicion, valor: c.valor }));
                Event.$emit(`filtros:cambio:${this.listKey}`, payload);
                this.recalcularTotal();
            },
        },
        mounted() {
            Event.$on('removerme', this.removerPorIndice = (index) => {
                this.condiciones.splice(index, 1);
                this.emitirCambio();
            });
            // Aplicar una vista guardada: reemplaza el set de condiciones.
            Event.$on(`vista:aplicar:${this.listKey}`, this.onVista = (config) => {
                this.condiciones = ((config && config.filtros) || []).map((c, i) => ({
                    id: 'v_' + i + '_' + c.campo,
                    campo: c.campo,
                    campoLabel: this.labelDeCampo(c.campo),
                    condicion: c.condicion,
                    valor: c.valor,
                }));
                this.emitirCambio();
            });
        },
        beforeDestroy() {
            Event.$off('removerme', this.removerPorIndice);
            Event.$off(`vista:aplicar:${this.listKey}`, this.onVista);
        },
    };
</script>

<style scoped>
    .filtros-listado {
        margin-bottom: 8px;
    }

    /* Barra compacta pegada a la tabla: botón + chips + total en una línea. */
    .filtros-barra {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .btn-filtros {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-filtros--activo {
        border-color: #3c8dbc;
        color: #3c8dbc;
    }

    /* Contador de condiciones activas dentro del botón. */
    .filtros-contador {
        display: inline-block;
        min-width: 18px;
        padding: 0 5px;
        line-height: 18px;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        background: #3c8dbc;
        border-radius: 10px;
    }

    .limpiar-todo {
        font-size: 13px;
        white-space: nowrap;
    }
    .total-coincidencias {
        margin-left: auto;
        white-space: nowrap;
    }

    /* El panel constructor abre/cierra con una transición suave. */
    .filtros-builder {
        margin-top: 8px;
    }
    .filtros-desplegar-enter-active,
    .filtros-desplegar-leave-active {
        transition: opacity .2s ease, transform .2s ease;
    }
    .filtros-desplegar-enter,
    .filtros-desplegar-leave-to {
        opacity: 0;
        transform: translateY(-6px);
    }
</style>
