<template>
    <div>
        <!-- Filtros -->
        <div class="reportes-filtros">
            <div class="form-group">
                <input type="text" class="form-control" v-model="filtros.q"
                       placeholder="Buscar por texto o persona…" @keyup.enter="aplicar">
            </div>
            <div class="form-group">
                <select class="form-control" v-model="filtros.status" @change="aplicar">
                    <option value="">Todos los estados</option>
                    <option value="nuevo">Nuevo</option>
                    <option value="triage">Triage</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="resuelto">Resuelto</option>
                    <option value="descartado">Descartado</option>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" v-model="filtros.type" @change="aplicar">
                    <option value="">Todos los tipos</option>
                    <option value="bug">Bug</option>
                    <option value="suggestion">Sugerencia</option>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" v-model="filtros.platform" @change="aplicar">
                    <option value="">Todas las plataformas</option>
                    <option value="web">Web</option>
                    <option value="app">App MiTECHO</option>
                    <option value="ambas">Web y App</option>
                </select>
            </div>
            <label class="reportes-pendientes">
                <input type="checkbox" v-model="filtros.pendientes" @change="aplicar">
                <i class="fa fa-comment"></i> Con respuesta pendiente
            </label>
            <button class="btn btn-primary" @click="aplicar"><i class="fa fa-search"></i> Buscar</button>
            <button class="btn btn-default" @click="limpiar">Limpiar</button>
        </div>

        <vuetable
            ref="vuetable"
            class="vuetable"
            :api-url="apiUrl"
            :fields="dataFields"
            pagination-path=""
            :css="css.table"
            :sort-order="dataSortOrder"
            :multi-sort="true"
            :per-page="25"
            :append-params="moreParams"
            @vuetable:pagination-data="onPaginationData"
        ></vuetable>

        <div class="vuetable-pagination">
            <vuetable-pagination-info ref="paginationInfo"
                info-class="pagination-info"
                info-template="Ítem {from} a {to} de {total}"
                no-data-template="No hay reportes para mostrar"
            ></vuetable-pagination-info>
            <vuetable-pagination ref="pagination"
                :css="css.pagination"
                @vuetable-pagination:change-page="onChangePage"
            ></vuetable-pagination>
        </div>

        <reporte-detalle></reporte-detalle>
    </div>
</template>

<script>
import Vue from 'vue';
import Vuetable from 'vuetable-2/src/components/Vuetable';
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';

export default {
    name: 'reportes-datatable',
    components: { Vuetable, VuetablePagination, VuetablePaginationInfo },
    props: ['apiUrl', 'fields', 'sortOrder'],
    data() {
        return {
            dataFields: [],
            dataSortOrder: [],
            filtros: this.filtrosVacios(),
            moreParams: {},
            css: {
                table: {
                    tableClass: 'table table-hover table-condensed',
                    ascendingIcon: 'fa fa-sort-amount-asc',
                    descendingIcon: 'fa fa-sort-amount-desc',
                    sortableIcon: 'fa fa-sort',
                },
                pagination: {
                    wrapperClass: 'pagination',
                    activeClass: 'active',
                    linkClass: 'page-link',
                    pageClass: 'page-item',
                },
            },
        };
    },
    methods: {
        onPaginationData(data) {
            this.$refs.pagination.setPaginationData(data);
            this.$refs.paginationInfo.setPaginationData(data);
        },
        onChangePage(page) {
            this.$refs.vuetable.changePage(page);
        },
        filtrosVacios() {
            return { q: '', status: '', type: '', platform: '', pendientes: false };
        },
        parametros() {
            return {
                q: this.filtros.q || undefined,
                status: this.filtros.status || undefined,
                type: this.filtros.type || undefined,
                platform: this.filtros.platform || undefined,
                pendientes: this.filtros.pendientes ? 1 : undefined,
            };
        },
        aplicar() {
            this.moreParams = this.parametros();
            Vue.nextTick(() => this.$refs.vuetable.refresh());
        },
        limpiar() {
            this.filtros = this.filtrosVacios();
            this.aplicar();
        },
        refrescar() {
            this.$refs.vuetable.refresh();
        },
    },
    created() {
        this.dataFields = JSON.parse(this.fields);
        this.dataSortOrder = JSON.parse(this.sortOrder);
        // El contador del menú linkea con ?pendientes=1: arrancar ya filtrado.
        if (/[?&]pendientes=1(&|$)/.test(window.location.search)) {
            this.filtros.pendientes = true;
            this.moreParams = this.parametros();
        }
    },
    mounted() {
        Event.$on('reporte:refrescar', this.refrescar);
    },
    beforeDestroy() {
        Event.$off('reporte:refrescar', this.refrescar);
    },
};
</script>

<style scoped>
.reportes-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 16px; }
.reportes-filtros .form-group { margin-bottom: 0; }
.reportes-pendientes { display: flex; align-items: center; gap: 6px; margin: 0 0 7px; font-weight: 600; color: #5b21b6; cursor: pointer; }
.reportes-filtros .form-control { min-width: 200px; }
</style>
