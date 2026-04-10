<template>
  <div>
    <simple-alert ref="loading"></simple-alert>

    <vuetable
      class="vuetable"
      ref="vuetable"
      v-bind:api-url="apiUrl"
      :fields="dataFields"
      pagination-path=""
      :css="css.table"
      :sort-order="dataSortOrder"
      :multi-sort="true"
      :perPage="25"
      :append-params="moreParams"
      @vuetable:pagination-data="onPaginationData"
      @vuetable:loading="mostrarLoadingAlert"
      @vuetable:loaded="ocultarLoadingAlert"
    >
      <!-- Columna convertido con botón de acción -->
      <template slot="convertido" slot-scope="props">
        <span v-if="props.rowData.convertido" class="text-success">
          <i class="fa fa-check"></i> {{ $t('backend.yes') }}
        </span>
        <button
          v-else
          class="btn btn-xs btn-warning"
          @click="convertir(props.rowData)"
          :disabled="convirtiendo === props.rowData.id"
        >
          <i class="fa fa-user-plus"></i> {{ $t('backend.convert_to_user') }}
        </button>
      </template>
    </vuetable>

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
import Vuetable from 'vuetable-2/src/components/Vuetable'
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import Vue from 'vue'
import VueEvents from 'vue-events'
import Simplert from 'vue2-simplert'

Vue.use(VueEvents)

export default {
  components: { Simplert, Vuetable, VuetablePagination, VuetablePaginationInfo },
  props: ['apiUrl', 'fields', 'sortOrder', 'campanaId'],
  data() {
    return {
      dataSortOrder: [],
      dataFields:    [],
      moreParams:    {},
      convirtiendo:  null,
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
        },
      },
    }
  },
  created() {
    this.dataSortOrder = JSON.parse(this.sortOrder)
    // Reemplazar la columna convertido con slot
    const parsed = JSON.parse(this.fields)
    parsed.forEach(f => {
      if (f.name === 'convertido') {
        f.name = '__slot:convertido'
      }
    })
    this.dataFields = parsed
  },
  methods: {
    onPaginationData(paginationData) {
      this.$refs.pagination.setPaginationData(paginationData)
      this.$refs.paginationInfo.setPaginationData(paginationData)
    },
    onChangePage(page) {
      this.$refs.vuetable.changePage(page)
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
      axios.post(`/admin/ajax/campanas/${rowData.id}/convertir`)
        .then(() => {
          this.$refs.vuetable.refresh()
        })
        .catch(e => {
          alert(e.response.data.message || this.$t('backend.error_guardando'))
        })
        .finally(() => {
          this.convirtiendo = null
        })
    },
  },
}
</script>
