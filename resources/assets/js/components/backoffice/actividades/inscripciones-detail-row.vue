<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <label>Fecha de Inscripción: </label>
                <span>{{ fechaInscripcion }}</span>
            </div>
            <div class="col-md-6">
                <label>Punto de Encuentro Seleccionado: </label>
                <span>{{ puntoEncuentro }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>Teléfono: </label>
                <span>{{rowData.telefonoMovil}}</span>
            </div>
            <div class="col-md-6">
                <label>E-mail: </label>
                <span>{{rowData.mail}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>Modificado por: </label>
                <span>{{rowData.modificado_por}}</span>
            </div>
            <div class="col-md-6">
                <label>Modificado en: </label>
                <span>{{rowData.modificado_en}}</span>
                &nbsp;<a @click="cargarAuditoria(rowData.id)" class="btn btn-primary btn-sm">Ver auditoría</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>Cantidad de Actividades Anteriores (Según filtro aplicado): </label>
                <span>{{rowData.cantActividades}}</span>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment';
moment.locale('es');
export default {
  props: {
    rowData: {
      type: Object,
      required: true
    },
    rowIndex: {
      type: Number
    }
  },
  computed: {
      fechaInscripcion: function () {
          return moment(this.rowData.fechaInscripcion).format("DD MMMM YYYY");
      },
      puntoEncuentro: function () {
          return this.rowData.punto + ", " + this.rowData.puntoLocalidad + ", " + this.rowData.puntoProvincia + ", " + this.rowData.puntoPais
      }
  },
  methods: {
    onClick (event) {
      console.log('my-detail-row: on-click', event.target)
    },
    cargarAuditoria: function(id) {
      Event.$emit('vuetable-cargarAuditoria', id);
    }
  },
}
</script>
