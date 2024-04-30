<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <label>Fecha de Inscripción: </label>
                <span>{{ fechaInscripcion }}</span>
                <br>
                <label>Punto de Encuentro: </label>
                <span>{{ puntoEncuentro }}</span>
                <br>
                <label>Móvil: </label>
                <span>{{ rowData.telefonoMovil }}</span>
                <br>
                <label>Email: </label>
                <span>{{ rowData.mail }}</span>
            </div>
            <div class="col-md-6">
                <label>Provincia: </label>
                <span>{{ rowData.pProvincia }}</span>
                <br>
                <label>Localidad: </label>
                <span>{{ rowData.pLocalidad }}</span>
                <br>
                <div v-if="rowData.voucherUrl">
                  <label>Archivo Pago: </label>
                  <a target="_blank" :href="'/'+rowData.voucherUrl"> Voucher</a>
                </div>
            </div> 
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <a :href="'/admin/usuarios/'+rowData.idPersona" class="btn btn-primary btn-xs" target="_blank" >
                  <i class="fa fa-user" ></i> &nbsp; Ver perfil
                </a>
                <a @click="cargarAuditoria(rowData.id)" class="btn btn-default btn-xs" ><i class="fa fa-history" ></i> &nbsp; Ver auditoría</a>
                <span style="color: #6d6d6d">Modificado por {{rowData.modificado_por}} @ {{rowData.modificado_en}}</span>
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
          return moment(this.rowData.fechaInscripcion).format("DD/MM/YYYY hh:mm");
      },
      puntoEncuentro: function () {
        console.log(this.rowData)
          let localidad = (this.rowData.puntoLocalidad == null) ? "" : this.rowData.puntoLocalidad + ", ";
          return this.rowData.punto + ", " + localidad + this.rowData.puntoProvincia + ", " + this.rowData.puntoPais
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
