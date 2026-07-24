<template>
    <div class="filtros-listado">
        <div class="row filtros-builder" v-show="abierto">
            <div class="col-md-12">
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

                        <select class="form-control" v-if="campo && campo.opciones" v-model="valor" style="min-width: 10em;">
                            <option value="">{{ $t('backend.choose') }}...</option>
                            <option v-for="o in campo.opciones" :key="o" :value="o">{{ o }}</option>
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
        </div>

        <div class="row" v-if="condiciones.length || total !== null">
            <div class="col-md-12 filtros-chips">
                <chip v-for="(c, index) in condiciones"
                      :key="c.id"
                      :index="index"
                      :valor="c.campoLabel + ' ' + opLabel(c.condicion) + ' ' + valorLabel(c)">
                </chip>
                <a v-if="condiciones.length" href="#" class="limpiar-todo" @click.prevent="limpiarTodo">
                    {{ $t('backend.clear_all') }}
                </a>
                <span class="pull-right text-info total-coincidencias" v-if="total !== null">
                    <strong>{{ total }}</strong> {{ $t('backend.records') }}
                </span>
            </div>
        </div>
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
            abierto: { type: Boolean, default: true },
        },
        data() {
            return {
                filtrables: [],
                condiciones: [],
                campoKey: '',
                operador: '',
                valor: '',
                preview: null,
                total: null,
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
        },
        watch: {
            campoKey() {
                this.operador = this.campo && this.campo.operadores.length ? this.campo.operadores[0] : '';
                this.valor = '';
                this.preview = null;
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
    .filtros-chips {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }
    .limpiar-todo {
        margin-left: 8px;
        font-size: 13px;
    }
    .total-coincidencias {
        margin-left: auto;
    }
</style>
