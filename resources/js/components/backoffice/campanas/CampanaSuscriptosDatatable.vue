<template>
  <div>
    <simple-alert ref="loading"></simple-alert>

    <div class="row" style="margin-bottom: 10px;">
      <div class="col-md-12">
        <a :href="exportUrl" class="btn btn-success pull-right">
          <i class="fa fa-download"></i> {{ $t('backend.export') }}
        </a>
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
    >
      <template slot="convertido" slot-scope="props">
        <!-- Email ya pertenece a un usuario existente -->
        <span v-if="props.rowData.persona_id">
          <span class="label label-info" style="font-size:0.85em;">
            <i class="fa fa-user"></i> {{ $t('backend.user_exists') }}
          </span>
          &nbsp;
          <a
            :href="'/admin/usuarios/' + props.rowData.persona_id"
            target="_blank"
            class="btn btn-xs btn-default"
            @click.stop
          >
            <i class="fa fa-external-link"></i> {{ $t('backend.view_person') }}
            <span v-if="props.rowData.persona_nombre"> — {{ props.rowData.persona_nombre }}</span>
          </a>
        </span>
        <!-- Convertido via botón -->
        <span v-else-if="props.rowData.convertido" class="text-success">
          <i class="fa fa-check"></i> {{ $t('backend.yes') }}
        </span>
        <!-- Sin usuario aún -->
        <button
          v-else
          class="btn btn-xs btn-warning"
          @click.stop="convertir(props.rowData)"
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
import CampanaSuscriptosDetailRow from './CampanaSuscriptosDetailRow'

Vue.use(VueEvents)
Vue.component('campana-suscriptos-detail-row', CampanaSuscriptosDetailRow)

export default {
  components: { Simplert, Vuetable, VuetablePagination, VuetablePaginationInfo },
  props: {
    apiUrl:    { type: String, required: true },
    exportUrl: { type: String, required: true },
    campanaId: { type: String, required: true },
  },
  data() {
    return {
      convirtiendo: null,
      moreParams:   {},
      dataSortOrder: [{ field: 'created_at', sortField: 'created_at', direction: 'desc' }],
      dataFields: [
        { name: 'nombre',     sortField: 'nombre',     title: this.$t('backend.name') },
        { name: 'apellido',   sortField: 'apellido',   title: this.$t('backend.last_name') },
        { name: 'mail',       sortField: 'mail',       title: this.$t('backend.email') },
        { name: 'telefono',   sortField: 'telefono',   title: this.$t('backend.phone') },
        { name: '__slot:convertido',                   title: this.$t('backend.converted') },
        { name: 'created_at', sortField: 'created_at', title: this.$t('backend.created_at'), callback: 'formatDate|DD-MM-YYYY' },
      ],
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
  methods: {
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
