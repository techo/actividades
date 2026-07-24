<template>
    <div class="agrupar-listado">
        <div class="form-inline agrupar-selector">
            <label style="margin-right: 6px;">{{ $t('backend.group_by') }}:</label>
            <select class="form-control" v-model="groupBy">
                <option value="">{{ $t('backend.no_grouping') }}</option>
                <option v-for="g in agrupables" :key="g.key" :value="g.key">{{ label(g) }}</option>
            </select>
        </div>

        <div class="recuento-grupo" v-if="groupBy && (buckets.length || sinValor)">
            <span class="recuento-titulo text-muted">{{ $t('backend.count_by_group') }}</span>
            <span class="recuento-chip" v-for="b in buckets" :key="b.valor">
                {{ b.label }} <strong>{{ b.total }}</strong>
            </span>
            <span class="recuento-chip recuento-sinvalor" v-if="sinValor">
                — <strong>{{ sinValor }}</strong>
            </span>
        </div>
    </div>
</template>

<script>
    /**
     * Selector "Agrupar por" + fila de recuento por grupo (facets) para los
     * listados configurables. Se alimenta de /config (campos `agrupables`) y de
     * /facets. Escucha `filtros:cambio:{listKey}` para recontar respetando los
     * filtros activos, y emite `agrupar:cambio:{listKey}` por si la tabla quiere
     * reaccionar al campo de agrupación.
     */
    export default {
        name: 'agrupar-listado',
        props: {
            listKey: { type: String, required: true },
            contextId: { required: true },
        },
        data() {
            return {
                agrupables: [],
                groupBy: '',
                buckets: [],
                sinValor: 0,
                condiciones: [],
            };
        },
        computed: {
            baseUrl() {
                return `/admin/ajax/listados/${this.listKey}/${this.contextId}`;
            },
        },
        watch: {
            groupBy(valor) {
                Event.$emit(`agrupar:cambio:${this.listKey}`, valor);
                this.recalcular();
            },
        },
        created() {
            axios.get(`${this.baseUrl}/config`).then(({ data }) => {
                this.agrupables = data.agrupables || [];
            });
            Event.$on(`filtros:cambio:${this.listKey}`, this.onFiltros = (condiciones) => {
                this.condiciones = Array.isArray(condiciones) ? condiciones : [];
                this.recalcular();
            });
            // Aplicar una vista guardada: setear el campo de agrupación.
            Event.$on(`vista:aplicar:${this.listKey}`, this.onVista = (config) => {
                this.groupBy = (config && config.group_by) || '';
            });
        },
        beforeDestroy() {
            Event.$off(`filtros:cambio:${this.listKey}`, this.onFiltros);
            Event.$off(`vista:aplicar:${this.listKey}`, this.onVista);
        },
        methods: {
            label(g) {
                return g.label && g.label.includes('.') ? this.$t(g.label) : g.label;
            },
            recalcular() {
                if (!this.groupBy) {
                    this.buckets = [];
                    this.sinValor = 0;
                    return;
                }
                axios.get(`${this.baseUrl}/facets`, {
                    params: {
                        group_by: this.groupBy,
                        condiciones: this.condiciones.map(c => JSON.stringify(c)),
                    },
                }).then(({ data }) => {
                    this.buckets = data.buckets || [];
                    this.sinValor = data.sin_valor || 0;
                });
            },
        },
    };
</script>

<style scoped>
    .agrupar-selector { margin-bottom: 8px; }
    .recuento-grupo {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        padding: 8px 0;
    }
    .recuento-titulo {
        text-transform: uppercase;
        font-size: 12px;
        margin-right: 6px;
    }
    .recuento-chip {
        background: #eef4fb;
        border: 1px solid #d5e3f2;
        border-radius: 16px;
        padding: 3px 10px;
        font-size: 13px;
        margin: 2px;
    }
    .recuento-sinvalor { background: #f2f2f2; border-color: #e0e0e0; }
</style>
