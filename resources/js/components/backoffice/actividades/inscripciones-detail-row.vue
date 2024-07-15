<template>
    <div>
        <div class="row">
            <div class="col-md-3 text-center">

              <img v-if="rowData.photo" class="imagen-perfil-redonda" :src="'/'+rowData.photo" alt="$t('backend.photo')">
              <img v-else src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-redonda" alt="User Image">
            </div>
              
            <div class="col-md-4">
                <label>{{ $t('backend.identifications') }}: </label>
                <span>{{ rowData.dni }}</span>
                <br>
                <label>{{ $t('backend.mobile') }}: </label>
                <a v-if="showWhatsapp" :href="'https://wa.me/' + rowData.telefonoMovil" target="_blank"><i class="fa fa-whatsapp" aria-hidden="true"></i></a>
                <span>{{ rowData.telefonoMovil }}</span>
                <br>
                <label>{{ $t('backend.email') }}: </label>
                <span>{{ rowData.mail }}</span>
                <br>
                <label>{{ $t('backend.age') }}: </label>
                <span>{{ edad }}</span>
                <br>
                <label>{{ $t('backend.role') }}: </label>
                <span>{{ rowData.nombreRol }}</span>
                <br>
                <label>{{ $t('backend.applicable_roles') }}: </label>
                <span>{{ rowData.roles_aplicados }}</span>
                
               
            </div>
            <div class="col-md-4">
                <label>{{ $t('backend.province') }}: </label>
                <span>{{ rowData.pProvincia }}</span>
                <br>
                <label>{{ $t('backend.location') }}: </label>
                <span>{{ rowData.pLocalidad }}</span>
                <br>
                <label>{{ $t('backend.registration_date') }}: </label>
                <span>{{ fechaInscripcion }}</span>
                <br>
                <label>{{ $t('backend.meeting_point') }}: </label>
                <span>{{ rowData.punto }}</span>

                <div v-if="rowData.voucherUrl">
                  <label>{{ $t('backend.payment_file') }}: </label>
                  <a target="_blank" :href="'/'+rowData.voucherUrl"> {{ $t('backend.voucher') }}</a>
                </div>
            </div> 
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <a :href="'/admin/usuarios/'+rowData.idPersona" class="btn btn-primary btn-xs" target="_blank" >
                  <i class="fa fa-user" ></i> &nbsp; {{ $t('backend.view_profile') }}
                </a>
                <a @click="cargarAuditoria(rowData.id)" class="btn btn-default btn-xs" ><i class="fa fa-history" ></i> &nbsp; {{ $t('backend.view_audit') }}</a>
                <span style="color: #6d6d6d">{{ $t('backend.modified_by') }} {{rowData.modificado_por}} @ {{rowData.modificado_en}}</span>
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
          let localidad = (this.rowData.puntoLocalidad == null) ? "" : this.rowData.puntoLocalidad + ", ";
          return this.rowData.punto + ", " + localidad + this.rowData.puntoProvincia + ", " + this.rowData.puntoPais
      },
      edad() {
        const fechaNacimiento = new Date(this.rowData.fechaNacimiento);
        const fechaActual = new Date();
        
        let edad = fechaActual.getFullYear() - fechaNacimiento.getFullYear();
        
        if (fechaActual.getMonth() < fechaNacimiento.getMonth() || 
            (fechaActual.getMonth() === fechaNacimiento.getMonth() && 
            fechaActual.getDate() < fechaNacimiento.getDate())) {
          edad--;
        }
        
        return edad;
      },
      showWhatsapp(){
        if (this.rowData.telefonoMovil.startsWith('+') && this.rowData.acepta_marketing){
          return true;
        }
        return false;
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
