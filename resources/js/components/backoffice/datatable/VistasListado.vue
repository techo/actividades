<template>
    <div class="vistas-listado">
        <ul class="nav nav-tabs vistas-tabs">
            <li v-for="v in todas" :key="v.id" :class="{ active: activeId === v.id }">
                <a href="#" @click.prevent="seleccionar(v)">
                    <span class="vista-punto" v-if="v.color" :style="{ background: v.color }"></span>
                    {{ v.nombre }}
                    <a v-if="!v.es_predefinida" href="#" class="vista-borrar" @click.prevent.stop="eliminar(v)" title="Eliminar">
                        <i class="fa fa-times"></i>
                    </a>
                </a>
            </li>
            <li>
                <a href="#" @click.prevent="abrirModal" class="vista-guardar">
                    <i class="fa fa-plus"></i> {{ $t('backend.save_as_view') }}
                </a>
            </li>
        </ul>

        <!-- Modal guardar como vista -->
        <div v-if="modal" class="vista-modal-backdrop" @click.self="modal = false">
            <div class="vista-modal">
                <h4><i class="fa fa-star-o"></i> {{ $t('backend.save_as_view') }}</h4>
                <p class="text-muted">{{ $t('backend.save_view_help') }}</p>

                <label>{{ $t('backend.view_name') }}</label>
                <input class="form-control" v-model="nombre" :placeholder="$t('backend.view_name_placeholder')">

                <label style="margin-top: 10px;">{{ $t('backend.color') }}</label>
                <div class="vista-colores">
                    <span v-for="c in colores" :key="c"
                          class="vista-color-opt" :class="{ sel: color === c }"
                          :style="{ background: c }" @click="color = c"></span>
                </div>

                <label style="margin-top: 10px;">{{ $t('backend.what_is_saved') }}</label>
                <ul class="vista-resumen text-muted">
                    <li>{{ resumenFiltros }}</li>
                    <li>{{ resumenGrupo }}</li>
                </ul>

                <div class="text-right" style="margin-top: 12px;">
                    <button class="btn btn-default" @click="modal = false">{{ $t('backend.cancel') }}</button>
                    <button class="btn btn-primary" :disabled="!nombre" @click="guardar">{{ $t('backend.save_view') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    /**
     * Pestañas de vistas guardadas (predefinidas + propias) de un listado
     * configurable. Al seleccionar una vista emite `vista:aplicar:{listKey}` con
     * su config (filtros + group_by); FiltrosListado/AgruparListado la aplican.
     * Rastrea el estado actual de filtros/agrupación para poder guardarlo.
     */
    export default {
        name: 'vistas-listado',
        props: {
            listKey: { type: String, required: true },
            contextId: { required: true },
        },
        data() {
            return {
                predefinidas: [],
                propias: [],
                activeId: null,
                modal: false,
                nombre: '',
                color: '#1f6feb',
                colores: ['#1f6feb', '#2ea043', '#f0b429', '#e5484d', '#8250df', '#0b7285'],
                filtrosActuales: [],
                groupByActual: null,
            };
        },
        computed: {
            baseUrl() {
                return `/admin/ajax/listados/${this.listKey}/${this.contextId}`;
            },
            todas() {
                return [...this.predefinidas, ...this.propias];
            },
            resumenFiltros() {
                const n = this.filtrosActuales.length;
                return n ? `${n} ${this.$t('backend.active_filters')}` : this.$t('backend.no_filters');
            },
            resumenGrupo() {
                return this.groupByActual
                    ? `${this.$t('backend.group_by')}: ${this.groupByActual}`
                    : this.$t('backend.no_grouping');
            },
        },
        created() {
            this.cargar();
            Event.$on(`filtros:cambio:${this.listKey}`, this.onFiltros = (c) => { this.filtrosActuales = Array.isArray(c) ? c : []; });
            Event.$on(`agrupar:cambio:${this.listKey}`, this.onAgrupar = (g) => { this.groupByActual = g || null; });
        },
        beforeDestroy() {
            Event.$off(`filtros:cambio:${this.listKey}`, this.onFiltros);
            Event.$off(`agrupar:cambio:${this.listKey}`, this.onAgrupar);
        },
        methods: {
            cargar() {
                return axios.get(`${this.baseUrl}/vistas`).then(({ data }) => {
                    this.predefinidas = data.predefinidas || [];
                    this.propias = data.propias || [];
                    if (this.activeId === null && this.predefinidas.length) {
                        this.activeId = this.predefinidas[0].id;
                    }
                });
            },
            seleccionar(v) {
                this.activeId = v.id;
                Event.$emit(`vista:aplicar:${this.listKey}`, v.config || { filtros: [], group_by: null });
            },
            abrirModal() {
                this.nombre = '';
                this.color = this.colores[0];
                this.modal = true;
            },
            guardar() {
                if (!this.nombre) return;
                axios.post(`${this.baseUrl}/vistas`, {
                    nombre: this.nombre,
                    color: this.color,
                    config: { filtros: this.filtrosActuales, group_by: this.groupByActual },
                }).then(() => {
                    this.modal = false;
                    this.cargar();
                });
            },
            eliminar(v) {
                if (!confirm(this.$t('backend.confirm_delete'))) return;
                axios.delete(`${this.baseUrl}/vistas/${v.id}`).then(() => {
                    if (this.activeId === v.id && this.predefinidas.length) {
                        this.seleccionar(this.predefinidas[0]);
                    }
                    this.cargar();
                });
            },
        },
    };
</script>

<style scoped>
    .vistas-tabs { margin-bottom: 10px; }
    .vista-punto {
        display: inline-block; width: 8px; height: 8px;
        border-radius: 50%; margin-right: 5px; vertical-align: middle;
    }
    .vista-borrar { margin-left: 6px; color: #999; }
    .vista-borrar:hover { color: #e5484d; }
    .vista-guardar { color: #1f6feb; }

    .vista-modal-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,.4);
        display: flex; align-items: center; justify-content: center; z-index: 1050;
    }
    .vista-modal {
        background: #fff; border-radius: 8px; padding: 20px;
        width: 460px; max-width: 92vw; box-shadow: 0 10px 40px rgba(0,0,0,.2);
    }
    .vista-colores { display: flex; gap: 8px; }
    .vista-color-opt {
        width: 26px; height: 26px; border-radius: 50%; cursor: pointer;
        border: 2px solid transparent;
    }
    .vista-color-opt.sel { border-color: #333; }
    .vista-resumen { padding-left: 18px; margin: 4px 0 0; }
</style>
