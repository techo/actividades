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
      @vuetable:cell-clicked="onCellClicked"
      @vuetable:pagination-data="onPaginationData"
      @vuetable:loading="mostrarLoadingAlert"
      @vuetable:loaded="ocultarLoadingAlert"
    ></vuetable>

    <div class="vuetable-pagination">
      <vuetable-pagination-info
        ref="paginationInfo"
        info-class="pagination-info"
        infoTemplate="Ítem {from} a {to} de {total}"
        noDataTemplate="No hay campañas para mostrar"
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
  props: ['apiUrl', 'fields', 'sortOrder', 'detailUrl'],
  data() {
    return {
      dataSortOrder: [],
      dataFields: [],
      moreParams: {},
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
    this.dataSortOrder = JSON.parse(this.sortOrder)
    this.dataFields    = JSON.parse(this.fields)
  },
  methods: {
    onPaginationData(paginationData) {
      this.$refs.pagination.setPaginationData(paginationData)
      this.$refs.paginationInfo.setPaginationData(paginationData)
    },
    onChangePage(page) {
      this.$refs.vuetable.changePage(page)
    },
    onCellClicked(data) {
      if (this.detailUrl) {
        window.location.href = this.detailUrl + data.id
      }
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
  },
}
</script>
