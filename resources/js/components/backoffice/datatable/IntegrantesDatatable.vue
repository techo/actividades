<template>
  <div>
    <integrante-modal :id-equipo="idEquipo" ></integrante-modal>
    <div class="row">
      <div class="col-md-12">
          <span class="pull-right">
              <button class="btn btn-primary" @click.prevent="desplegarModal()">Crear <i class="fa fa-plus"></i></button>
          </span>
      </div>
    </div>
    <usuarios-filter-bar v-bind:placeholder-text="dataPlaceholderText"></usuarios-filter-bar>
    <vuetable
      class="vuetable"
      ref="vuetable"
      v-bind:api-url="apiUrl"
      :fields="dataFields"
      pagination-path=""
      v-bind:noDataTemplate="$t('frontend.empty_records')"
      :css="css.table"
      :sort-order="dataSortOrder"
      :multi-sort="true"
      :perPage="25"
      :append-params="moreParams"
      @vuetable:cell-clicked="onCellClicked"
      @vuetable:pagination-data="onPaginationData"
    ></vuetable>
    <div class="vuetable-pagination">
      <vuetable-pagination-info
              ref="paginationInfo"
              infoTemplate="Ítem {from} a {to} de {total}"
              info-class="pagination-info"
              v-bind:noDataTemplate="$t('frontend.empty_records')"
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
  import Vuetable from 'vuetable-2/src/components/Vuetable'
  import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
  import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
  import Vue from 'vue'
  import VueEvents from 'vue-events'
  import FilterBar from './FilterBar'

  Vue.use(VueEvents);
  Vue.component('filter-bar', FilterBar);

export default {
  components: {
    Vuetable,
    VuetablePagination,
    VuetablePaginationInfo,
  },
    props: ['apiUrl', 'fields', 'sortOrder', 'placeholder-text', 'detail-url', 'idEquipo'],
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
        moreParams: {}
    }
  },
  methods: {
      getLocalidad (objLocalidad) {
          return objLocalidad.localidad + ', ' + objLocalidad.provincia;
      },
    onPaginationData (paginationData) {
      this.$refs.pagination.setPaginationData(paginationData);
      this.$refs.paginationInfo.setPaginationData(paginationData);
    },
    onChangePage (page) {
      this.$refs.vuetable.changePage(page)
    },
    desplegarModal() {
      Event.$emit('integrante:crear');
    },
    onCellClicked (data, field, event) { 
      console.log(data);
      Event.$emit('integrante:editar', data);
    },
      bold: function(value) {
          return '<b>' + value + '</b>';
      }
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

.align-bottom {
    position:absolute;
    bottom: 0;
}

.vuetable-component {
    padding: 0.4rem
}

/*
.vuetable tr {
  cursor: pointer;
}

.vuetable tr td:hover{
 text-decoration: underline;
}*/
</style>
