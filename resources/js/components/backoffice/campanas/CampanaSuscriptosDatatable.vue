<template>
  <div>
    <simple-alert ref="loading"></simple-alert>

    <div class="row" style="margin-bottom: 10px;">
      <div class="col-md-12">
        <a :href="exportUrl" class="btn btn-success pull-right">
          <i class="fa fa-download"></i> {{ $t('backend.export') }}
        </a>
        <column-selector-panel list-key="suscriptos" :context-id="campanaId" class="pull-right" style="margin-right: 8px;"></column-selector-panel>
      </div>
    </div>

    <vuetable
      class="vuetable"
      ref="vuetable"
      v-bind:api-url="apiUrl"
      :fields="dataFields"
      pagination-path=""
      :css="css.table"
      :sort-order="dataSortOrder"
      :multi-sort="true"
      :per-page="25"
      :append-params="moreParams"
      detail-row-component="campana-suscriptos-detail-row"
      @vuetable:pagination-data="onPaginationData"
      @vuetable:loading="mostrarLoadingAlert"
      @vuetable:loaded="ocultarLoadingAlert"
      @vuetable:cell-clicked="onCellClicked"
    ></vuetable>

    <div class="vuetable-pagination">
      <vuetable-pagination-info
        ref="paginationInfo"
        info-class="pagination-info"
        infoTemplate="Ítem {from} a {to} de {total}"
        noDataTemplate="No hay suscriptos para mostrar"
      ></vuetable-pagination-info>
      <vuetable-pagination
        ref="pagination"
        :css="css.pagination"
        @vuetable-pagination:change-page="onChangePage"
      ></vuetable-pagination>
    </div>
  </div>
</template>

<script>
import Vuetable from '../datatable/Vuetable'
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import Vue from 'vue'
import VueEvents from 'vue-events'
import Simplert from 'vue2-simplert'
import CampanaSuscriptosDetailRow from './CampanaSuscriptosDetailRow'
import columnasConfigurables from '../../../mixins/columnasConfigurables'

Vue.use(VueEvents)
Vue.component('campana-suscriptos-detail-row', CampanaSuscriptosDetailRow)

export default {
  components: { Simplert, Vuetable, VuetablePagination, VuetablePaginationInfo },
  mixins: [columnasConfigurables],
  props: {
    apiUrl:    { type: String, required: true },
    exportUrl: { type: String, required: true },
    campanaId: { type: String, required: true },
    fields:    { type: String, required: true },
    sortOrder: { type: String, default: '[]' },
  },
  data() {
    return {
      listadoKey: 'suscriptos',
      convirtiendo: null,
      moreParams:   { condiciones: [] },
      dataSortOrder: [{ field: 'created_at', sortField: 'created_at', direction: 'desc' }],
      dataFields: [],
      css: {
        table: {
          tableClass: 'table table-hover table-condensed',
          ascendingIcon: 'glyphicon glyphicon-chevron-up',
          descendingIcon: 'glyphicon glyphicon-chevron-down',
        },
        pagination: {
          wrapperClass: 'pagination',
          activeClass: 'active',
          disabledClass: 'disabled',
          pageClass: 'page',
          linkClass: 'link',
          icons: { first: '', prev: '', next: '', last: '' },
        },
      },
    }
  },
  created() {
    // Fields dinámicos (fijas + defaults) provistos por el catálogo.
    this.dataFields = JSON.parse(this.fields).map((f) => {
      const field = { ...f }
      if (field.title) field.title = this.translateField(field.title)
      return field
    })
    const so = JSON.parse(this.sortOrder)
    if (so && so.length) this.dataSortOrder = so

    // Filtros genéricos (<filtros-listado>) → reemplaza el set de condiciones.
    Event.$on('filtros:cambio:suscriptos', this.aplicarFiltros)
    // Conversión a usuario disparada desde la celda.
    Event.$on('suscriptos:convertir', this.convertir)
  },
  beforeDestroy() {
    Event.$off('filtros:cambio:suscriptos', this.aplicarFiltros)
    Event.$off('suscriptos:convertir', this.convertir)
  },
  methods: {
    aplicarFiltros(condiciones) {
      this.moreParams.condiciones = Array.isArray(condiciones) ? condiciones : []
      this.$refs.vuetable.resetData()
      Vue.nextTick(() => this.$refs.vuetable.refresh())
    },
    onPaginationData(paginationData) {
      this.$refs.pagination.setPaginationData(paginationData)
      this.$refs.paginationInfo.setPaginationData(paginationData)
    },
    onChangePage(page) {
      this.$refs.vuetable.changePage(page)
    },
    onCellClicked(data) {
      this.$refs.vuetable.toggleDetailRow(data.id)
    },
    mostrarLoadingAlert() {
      this.$refs.loading.openSimplert({
        title: 'Espera...',
        message: '<i class="fa fa-spinner fa-spin fa-4x"></i>',
        hideAllButton: true,
        isShown: true,
        disableOverlayClick: true,
        type: '',
      })
    },
    ocultarLoadingAlert() {
      this.$refs.loading.justCloseSimplert()
    },
    convertir(rowData) {
      if (!confirm(this.$t('backend.confirm_convert_to_user'))) return
      this.convirtiendo = rowData.id
      axios.post('/admin/ajax/campanas/' + rowData.id + '/convertir')
        .then(() => {
          this.$refs.vuetable.refresh()
        })
        .catch((e) => {
          alert(e.response && e.response.data && e.response.data.message
            ? e.response.data.message
            : this.$t('backend.error_guardando'))
        })
        .finally(() => {
          this.convirtiendo = null
        })
    },
  },
}
</script>
