<template>
  <div>
    <filter-bar v-bind:placeholder-text="dataPlaceholderText"></filter-bar>
    <vuetable ref="vuetable"
      v-bind:api-url="apiUrl"
      :fields="dataFields"
      pagination-path=""
      :css="css.table"
      :sort-order="dataSortOrder"
      :multi-sort="true"
      :append-params="moreParams"
      @vuetable:cell-clicked="onCellClicked"
      @vuetable:pagination-data="onPaginationData"
    ></vuetable>
    <div class="vuetable-pagination">
      <vuetable-pagination-info ref="paginationInfo"
        info-class="pagination-info"
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
import CustomActions from './CustomActions'
import DetailRow from './DetailRow'
import FilterBar from './FilterBar'

Vue.use(VueEvents);
Vue.component('custom-actions', CustomActions)
Vue.component('my-detail-row', DetailRow)
Vue.component('filter-bar', FilterBar)

export default {
  components: {
    Vuetable,
    VuetablePagination,
    VuetablePaginationInfo,
  },
    props: ['apiUrl', 'fields', 'sortOrder', 'placeholder-text'],
    data () {
    return {
        dataPlaceholderText: this.placeholderText,
        dataSortOrder: [],
        dataFields: [],
        // apiUrl: this.apiUrl,
        // fields: [
        // {
        //   name: '__sequence',
        //   title: '#',
        //   titleClass: 'text-right',
        //   dataClass: 'text-right'
        // },
        // {
        //   name: '__checkbox',
        //   titleClass: 'text-center',
        //   dataClass: 'text-center',
        // },
        // {
        //   name: 'nombreActividad',
        //   title: 'Nombre',
        //   sortField: 'nombreActividad',
        // },
        // {
        //   name: 'nombreUnidad',
        //   sortField: 'nombreUnidad',
        //   title: 'Oficina'
        // },
        // {
        //   name: 'fechaInicio',
        //   sortField: 'fechaInicio',
        //   titleClass: 'text-center',
        //   dataClass: 'text-center',
        //   title: 'Fecha Inicio',
        //   callback: 'formatDate|DD-MM-YYYY'
        // },
        //   {
        //   name: 'fechaFin',
        //   sortField: 'fechaFin',
        //   titleClass: 'text-center',
        //   dataClass: 'text-center',
        //   title: 'Fecha Fin',
        //   callback: 'formatDate|DD-MM-YYYY'
        // },
        //   {
        //   name: 'tipoActividad',
        //   sortField: 'tipoActividad',
        //   titleClass: 'text-center',
        //   dataClass: 'text-center',
        //   title: 'Tipo',
        // },
        //   {
        //   name: 'estadoConstruccion',
        //   sortField: 'estadoConstruccion',
        //   titleClass: 'text-center',
        //   dataClass: 'text-center',
        //   title: 'Estado',
        // },
        // {
        //   name: 'nickname',
        //   sortField: 'nickname',
        //   callback: 'allcap'
        // },
        // {
        //   name: 'gender',
        //   sortField: 'gender',
        //   titleClass: 'text-center',
        //   dataClass: 'text-center',
        //   callback: 'genderLabel'
        // },
        // {
        //   name: 'salary',
        //   sortField: 'salary',
        //   titleClass: 'text-center',
        //   dataClass: 'text-right',
        //   callback: 'formatNumber'
        // },
        // {
        //   name: '__component:custom-actions',
        //   title: 'Actions',
        //   titleClass: 'text-center',
        //   dataClass: 'text-center'
        // }
        // ],
        css: {
        table: {
          tableClass: 'table table-bordered table-striped table-hover',
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
    estadoBadge (value) {
      return value === 'M'
        ? '<span class="label label-success"><i class="glyphicon glyphicon-star"></i> Male</span>'
        : '<span class="label label-danger"><i class="glyphicon glyphicon-heart"></i> Female</span>'
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
      console.log('cellClicked: ', field.name)
      this.$refs.vuetable.toggleDetailRow(data.id)
    },
  },
  created()  {
      this.dataSortOrder = JSON.parse(this.sortOrder);
      this.dataFields = JSON.parse(this.fields);
  },
  events: {
    'filter-set' (filterText) {
      this.moreParams = {
        filter: filterText
      };
      Vue.nextTick( () => this.$refs.vuetable.refresh() )
    },
    'filter-reset' () {
      this.moreParams = {};
      Vue.nextTick( () => this.$refs.vuetable.refresh() )
    }
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
</style>
