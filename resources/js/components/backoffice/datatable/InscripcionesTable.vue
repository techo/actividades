<template>
  <div>

    <simple-alert ref="loading"></simple-alert>
    <filter-bar v-bind:placeholder-text="dataPlaceholderText" :idActividad="actividad" ></filter-bar>
    <div class="table-responsive">
      <vuetable
              class="vuetable"
              ref="inscripcionesVuetable"
              v-bind:api-url="apiUrl"
              :fields="dataFields"
              pagination-path=""
              :css="css.table"
              :sort-order="dataSortOrder"
              :multi-sort="true"
              :append-params="moreParams"
              detail-row-component="inscripciones-detail-row"
              @vuetable:pagination-data="onPaginationData"
              @vuetable:checkbox-toggled="checkboxToggledEmitter"
              @vuetable:checkbox-toggled-all="checkboxToggledEmitter"
              @vuetable:cell-clicked="mostrarDetalle"
      ></vuetable>
    </div>
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
import DetailRow from '../actividades/inscripciones-detail-row'
import InscripcionesFilterBar from './InscripcionesFilterBar'
import InscripcionesToolbar from '../actividades/inscripciones-toolbar'
import Pago from './Pago';
import Asistencia from './Asistencia';
import Confirma from './Confirma';
import ActualizarInscripcion from './actualizarInscripcion';
import axios from 'axios';
import Simplert from 'vue2-simplert';


Vue.use(VueEvents);
  Vue.component('custom-actions', CustomActions);
  Vue.component('inscripciones-detail-row', DetailRow);
  Vue.component('inscripciones-filter-bar', InscripcionesFilterBar);
  Vue.component('inscripciones-toolbar', InscripcionesToolbar);
  Vue.component('asistencia', Asistencia);
  Vue.component('pago', Pago);
  Vue.component('confirma', Confirma);
  Vue.component('actualizar-inscripcion', ActualizarInscripcion);
  Vue.component('simple-alert', Simplert);

export default {
  components: {
    Vuetable,
    'filter-bar': InscripcionesFilterBar,
    VuetablePagination,
    VuetablePaginationInfo,
  },
    props: ['apiUrl', 'fields', 'sortOrder', 'placeholder-text', 'detailUrl', 'actividad'], //TODO: Se puede quitar la prop actividad, y tomar el id de la actividad desde la ruta
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
        moreParams: {},
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
      this.$refs.inscripcionesVuetable.changePage(page)
    },
    mostrarDetalle (data, field, event) {
      this.$refs.inscripcionesVuetable.toggleDetailRow(data.id)
    },
     // Custom
      axiosPost(url, fCallback, params = []) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.post(url, params)
            .then(response => {
                fCallback(response.data, this);
                Event.$emit('success');
                this.readonly = true;
            })
            .catch((error) => {
                Event.$emit('error');
                // Error
                console.info('Error en: ' + url);
                console.error(error.response.status);
                if (error.request) {
                    console.error(error.request);
                } else {
                    console.error('Error', error.message);
                }
                console.error(error.config);
            });
      },
      agregarCondicion(condicion){
          this.moreParams.condiciones.push({campo: condicion.campo, condicion: condicion.condicion, valor: condicion.valor});
          Vue.nextTick( () => this.$refs.inscripcionesVuetable.refresh());
      },
      removerCondicion(condicion){
          this.moreParams.condiciones.splice(condicion.index, 1);
          Vue.nextTick( () => this.$refs.inscripcionesVuetable.refresh());
      },
      checkboxToggledEmitter: function (status) {
          let info = {
              status: status,
              count: this.$refs.inscripcionesVuetable.selectedTo.length
          };
          Event.$emit('checkbox-toggled', info);
      },
      asignarRol: function (rol) {
          let url = this.apiUrl + 'asignar/rol';
          let params = {
              rol: rol,
              actividad: this.actividad, //TODO: remover y usar id de ruta
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
              Vue.nextTick( () => self.$refs.inscripcionesVuetable.refresh());
              Event.$emit('mensaje-success', data);
              Event.$emit('vuetable-actualizarTabla');
          },
          params);
      },
      asignarGrupo: function (grupo) {
          let url = this.apiUrl + 'asignar/grupo';
          let params = {
              grupo: grupo,
              actividad: this.actividad, //TODO: remover y usar id de ruta
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
                  Vue.nextTick( () => self.$refs.inscripcionesVuetable.refresh());
                  Event.$emit('mensaje-success', data);
                  Event.$emit('vuetable-actualizarTabla');
              },
              params);
      },
      asignarPunto: function (punto) {
          let url = this.apiUrl + 'asignar/punto';
          let params = {
              punto: punto,
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
                  Vue.nextTick( () => self.$refs.inscripcionesVuetable.refresh());
                  Event.$emit('mensaje-success', data);
                  Event.$emit('vuetable-actualizarTabla');
              },
              params);
      },
      desinscribir: function (punto) {
          let url = this.apiUrl + 'desinscribir';
          let params = {
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
                  Event.$emit('inscripciones-actualizar-tabla');
                  Event.$emit('mensaje-success', data);
                  Event.$emit('vuetable-actualizarTabla');
              },
              params);
      },
      cambiarEstado: function (estado) {
          let url = this.apiUrl + 'cambiar/estado';
          let params = {
              estado: estado,
              actividad: this.actividad, //TODO: remover y usar id de ruta
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
                  Vue.nextTick( () => self.$refs.inscripcionesVuetable.refresh());
                  Event.$emit('mensaje-success', data);
              },
              params);
      },
      cambiarConfirmacion: function (confirmacion) {
          let url = this.apiUrl + 'cambiar/confirmacion';
          let params = {
              confirmacion: confirmacion,
              actividad: this.actividad,
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
                  Vue.nextTick( () => self.$refs.inscripcionesVuetable.refresh());
                  Event.$emit('confirmacion:cambio');
                  Event.$emit('mensaje-success', data);
              },
              params);
      },
      cambiarPago: function (pago) {
          let url = this.apiUrl + 'cambiar/pago';
          let params = {
              pago: pago,
              actividad: this.actividad,
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
                  Vue.nextTick( () => self.$refs.inscripcionesVuetable.refresh());
                  Event.$emit('pago:cambio');
                  Event.$emit('mensaje-success', data);
              },
              params);
      },
      cambiarAsistencia: function (asistencia) {
          let url = this.apiUrl + 'cambiar/asistencia';
          let params = {
              asistencia: asistencia,
              actividad: this.actividad, //TODO: remover y usar id de ruta
              inscripciones: this.$refs.inscripcionesVuetable.selectedTo
          };
          this.axiosPost(url, function (data, self) {
                  Vue.nextTick( () => self.$refs.inscripcionesVuetable.refresh());
                  Event.$emit('asistencia:cambio');
                  Event.$emit('mensaje-success', data);
              },
              params);
      },
      procesarArchivo: function (archivo) {
          let url = this.apiUrl + 'procesar/archivo';
          let formData = new FormData();
          this.mostrarLoadingAlert();
          formData.append('archivo', archivo);
          axios.post(url, formData, {headers: {'Content-Type': 'multipart/form-data'}})
              .then(function (data, self) {
                      // loading(false);
                      Event.$emit('inscripciones-actualizar-tabla');
                      if(data.data.errores > 0) {
                          Event.$emit('mensaje-warning', data.data);
                      } else {
                          Event.$emit('mensaje-success', data.data);
                      }
                      Event.$emit('vuetable-actualizarTabla');
                      Event.$emit('ocultar-Loading-alert');
              })
              .catch(function (data, self) {
                  Event.$emit('mensaje-error', data);
                  Event.$emit('ocultar-Loading-alert');
              });
      },
      actualizarInscripcionesTable: function () {
          Vue.nextTick( () => {
            this.$refs.inscripcionesVuetable.refresh();
            this.$refs.inscripcionesVuetable.selectedTo = [];
          });
      },
      mostrarLoadingAlert() {
          this.$refs.loading.openSimplert({
              title: 'Espera...',
              message: "<i class=\"fa fa-spinner fa-spin fa-4x\"></i>",
              hideAllButton: true,
              isShown: true,
              disableOverlayClick: true,
              type: ''
          })
      },
      ocultarLoadingAlert: function () {
          this.$refs.loading.justCloseSimplert();
      },

      cargarAuditoria: function(id) {
        Event.$emit('cargarAuditoria', {tabla: 'inscripcion', id: id});
      },
  },
  created()  {
      this.dataSortOrder = JSON.parse(this.sortOrder);
      this.dataFields = JSON.parse(this.fields);
      // Custom
      Event.$on('agregar-condicion', this.agregarCondicion);
      Event.$on('remover-condicion', this.removerCondicion);
      Event.$on('rol-asignado', this.asignarRol);
      Event.$on('grupo-asignado', this.asignarGrupo);
      Event.$on('punto-asignado', this.asignarPunto);
      Event.$on('cambiar-estado', this.cambiarEstado);
      Event.$on('cambiar-pago', this.cambiarPago);
      Event.$on('cambiar-confirmacion', this.cambiarConfirmacion);
      Event.$on('cambiar-asistencia', this.cambiarAsistencia);
      Event.$on('desinscripto', this.desinscribir);
      Event.$on('inscripciones:archivo-seleccionado', this.procesarArchivo);
      Event.$on('inscripciones-actualizar-tabla', this.actualizarInscripcionesTable);
      Event.$on('ocultar-Loading-alert', this.ocultarLoadingAlert);

      Event.$on('vuetable-cargarAuditoria', this.cargarAuditoria);

      this.moreParams.condiciones = [];
  },
  events: {
    'filter-set-inscripciones' (filterText) {
      this.moreParams.filter = filterText;
      Vue.nextTick( () => this.$refs.inscripcionesVuetable.refresh() )
    },
    'filter-reset-inscripciones' () {
      this.moreParams.filter = null;
      Vue.nextTick( () => this.$refs.inscripcionesVuetable.refresh() )
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
