<template>
  <div>
    <grupos-filter-bar v-bind:placeholder-text="dataPlaceholderText" ref="gruposToolbar"></grupos-filter-bar>
    <vuetable-miembros
      class="vuetable"
      ref="vuetableMiembros"
      v-bind:api-url="url"
      :fields="dataFields"
      pagination-path=""
      :css="css.table"
      :sort-order="dataSortOrder"
      :multi-sort="true"
      :append-params="moreParams"
      @vuetable:cell-clicked="onCellClicked"
      @vuetable:pagination-data="onPaginationData"
      @vuetable:checkbox-toggled="checkboxToggledEmitter"
      @vuetable:checkbox-toggled-all="checkboxToggledAllEmitter"
    ></vuetable-miembros>
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

  import accounting from 'accounting'
  import moment from 'moment'
  import Vuetable from 'vuetable-2/src/components/Vuetable'; //https://github.com/ratiw/vuetable-2-tutorial/wiki
  import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
  import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
  import Vue from 'vue'
  import VueEvents from 'vue-events'
  import CustomActions from '../datatable/CustomActions'
  import DetailRow from '../datatable/DetailRow'
  import GruposFilterBar from '../datatable/GruposFilterBar';


  Vue.use(VueEvents);
  Vue.component('custom-actions', CustomActions);
  Vue.component('my-detail-row', DetailRow);
  Vue.component('grupos-filter-bar', GruposFilterBar);

export default {
    components: {
        'vuetable-miembros': Vuetable,
        VuetablePagination,
        VuetablePaginationInfo,
    },
    props: ['apiUrl', 'fields', 'sortOrder', 'placeholder-text', 'id-grupo-raiz', 'id-actividad'],
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
        moreParams: {},
        idGrupoActual: 1
    }
  },
  methods: {
        getIcon (value) {
            return value==='grupo'
                ? '<span class="label label-success"><i class="fa fa-users"></i></span>'
                : '<span class="label label-info"><i class="fa fa-user"></i></span>'
        },
        getRol (value) {
            return value
                ? '<p>' + value + '</p>'
                : '<p>&nbsp;</p>'
        },
        onPaginationData (paginationData) {
          this.$refs.pagination.setPaginationData(paginationData);
          this.$refs.paginationInfo.setPaginationData(paginationData);
        },
        onChangePage (page) {
          this.$refs.vuetableMiembros.changePage(page)
        },
        onCellClicked (data, field, event) {
            if (data.tipo === 'grupo') {
                this.idGrupoActual = data.id;
                Vue.nextTick( () => this.$refs.vuetableMiembros.refresh());
                Event.$emit('vuetable-addToBreadcrumb', data);
            }

            if (data.tipo === 'persona') {
                Event.$emit('vuetable-verVoluntario', data);
            }
        },
        checkboxToggledEmitter (status, obj) {
            Event.$emit('MiembrosTabla:miembro-seleccionado', {'status': status, 'obj': obj });
        },
        checkboxToggledAllEmitter(valor) {
            let data = {tableData: this.$refs.vuetableMiembros.tableData, selected: valor};
            Event.$emit('MiembrosTabla:miembro-seleccionado-todos', data);
        },
        actualizarTabla (grupo) {
            if (grupo !== undefined) { this.idGrupoActual = grupo.id; }
            Vue.nextTick( () => {
              this.$refs.vuetableMiembros.refresh();
              this.$refs.vuetableMiembros.selectedTo = [];
            });
        }
    },
    created()  {
        this.dataSortOrder = JSON.parse(this.sortOrder);
        this.dataFields = JSON.parse(this.fields);
        this.idGrupoActual = this.idGrupoRaiz;
        this.moreParams.idActividad = JSON.parse(this.idActividad);
        Event.$on('vuetable-actualizarTabla', this.actualizarTabla);
    },
    events: {
        'filter-set-miembros' (filterText) {
          // this.moreParams = {
          //   filter: filterText
          // };
          this.moreParams.filter = filterText;
          Vue.nextTick( () => this.$refs.vuetableMiembros.refresh() )
        },
        'filter-reset-miembros' () {
          this.moreParams.filter = null;
          Vue.nextTick( () => this.$refs.vuetableMiembros.refresh() )
        }
    },
    computed: {
      url: function () {
          return '/admin/ajax/grupos/'+ this.idGrupoActual +'/miembros';
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

  .vuetable tr {
    cursor: pointer;
  }

  .vuetable tr td:hover{
    text-decoration: underline;
  }
</style>
