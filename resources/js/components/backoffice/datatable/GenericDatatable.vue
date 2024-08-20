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
      <vuetable-pagination-info ref="paginationInfo"
        info-class="pagination-info"
        infoTemplate="Ítem {from} a {to} de {total}"
        noDataTemplate="No hay ítems para mostrar"
      ></vuetable-pagination-info>
      <vuetable-pagination ref="pagination"
        :css="css.pagination"
        @vuetable-pagination:change-page="onChangePage"
      ></vuetable-pagination>
    </div>
  </div>
</template>

<script>
    //https://github.com/ratiw/vuetable-2-tutorial/wiki
  import accounting from 'accounting'
  import moment from 'moment'
  import Vuetable from 'vuetable-2/src/components/Vuetable'
  import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
  import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
  import Vue from 'vue'
  import VueEvents from 'vue-events'
  import CustomActions from '../datatable/CustomActions'
  import DetailRow from '../datatable/DetailRow'
  import FilterBar from './OficinasFilterBar'
  import Simplert from 'vue2-simplert';


  Vue.use(VueEvents);
  Vue.component('custom-actions', CustomActions);
  Vue.component('my-detail-row', DetailRow);

export default {
  components: {
    Simplert,
    Vuetable,
    VuetablePagination,
    VuetablePaginationInfo,
  },
    props: ['apiUrl', 'fields', 'sortOrder', 'placeholder-text', 'detailUrl'],
    data () {
    return {
        dataPlaceholderText: this.placeholderText,
        dataSortOrder: [],
        dataFields: [],
        css: {
        table: {
            tableClass: 'table table-hover table-condensed',
          ascendingIcon: 'glyphicon glyphicon-chevron-up',
          descendingIcon: 'glyphicon glyphicon-chevron-down'
        },
        pagination: {
          wrapperClass: 'pagination',
          activeClass: 'active',
          disabledClass: 'disabled',
          pageClass: 'page',
          linkClass: 'link',
          icons: {
            first: '',
            prev: '',
            next: '',
            last: '',
          },
        },
        icons: {
          first: 'glyphicon glyphicon-step-backward',
          prev: 'glyphicon glyphicon-chevron-left',
          next: 'glyphicon glyphicon-chevron-right',
          last: 'glyphicon glyphicon-step-forward',
        },
        },
        // sortOrder: [
        // { field: 'nombreActividad', sortField: 'nombreActividad', direction: 'asc'}
        // ],
        moreParams: {}
    }
  },
  methods: {
    allcap (value) {
      return value.toUpperCase()
    },
    formatNumber (value) {
      return accounting.formatNumber(value, 2)
    },
    formatDate (value, fmt = 'D MMM YYYY') {
      return (value == null)
        ? ''
        : moment(value, 'YYYY-MM-DD').format(fmt)
    },
    onPaginationData (paginationData) {
      this.$refs.pagination.setPaginationData(paginationData);
      this.$refs.paginationInfo.setPaginationData(paginationData);
    },
    onChangePage (page) {
      this.$refs.vuetable.changePage(page)
    },
    onCellClicked (data, field, event) {
        if (this.detailUrl !== undefined) {
            window.location.href = this.detailUrl + data.id;
        }
      // this.$refs.vuetable.toggleDetailRow(data.id);
      this.$emit('onClickRow', data);
    },
    mostrarLoadingAlert () {
        this.$refs.loading.openSimplert({
              title: 'Espera...',
              message: "<i class=\"fa fa-spinner fa-spin fa-4x\"></i>",
              hideAllButton: true,
              isShown: true,
              disableOverlayClick: true,
              type: ''
          })
    },
    ocultarLoadingAlert () {
        this.$refs.loading.justCloseSimplert();
    },
  },
  created()  {
      this.dataSortOrder = JSON.parse(this.sortOrder);
      this.dataFields = JSON.parse(this.fields);
  },
  events: {
    'filter-set' (filterText) {
      this.moreParams = {
        oficina: filterText,
      };
      Vue.nextTick( () => this.$refs.vuetable.refresh() )
    },
    'filter-reset' () {
      this.moreParams = {};
      Vue.nextTick( () => this.$refs.vuetable.refresh() )
    },
  }
}
</script>
<style>
.pagination {
  margin: 0;
  float: right;
}
.pagination a.page {
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 10px;
  margin-right: 2px;
    cursor: pointer;
}
.pagination a.page.active {
  color: white;
  background-color: #337ab7;
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 10px;
  margin-right: 2px;
}
.pagination a.btn-nav {
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 7px;
  margin-right: 2px;
    cursor: pointer;
}
.pagination a.btn-nav.disabled {
  color: lightgray;
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 5px 7px;
  margin-right: 2px;
  cursor: not-allowed;
}
.pagination-info {
  float: left;
}

.vuetable tr {
  cursor: pointer;
}

.vuetable tr td:hover{
  text-decoration: underline;
}
</style>
