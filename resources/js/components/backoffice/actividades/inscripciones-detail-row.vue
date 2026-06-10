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
                <label>{{ $t('backend.registration_type') }}: </label>
                <span v-for="tipoInscripcion in tiposInscripcionSinComillas" class="label label-primary">
                    {{ $t('backend.tipo_voluntariado_options.' + tipoInscripcion) }}
                </span>
                <br>
                <label>{{ $t('backend.applicable_roles') }}: </label>
                <span v-for="item in items" class="label label-primary">
                    {{ $t('backend.roles_actividad_options.' + item) }}
                </span>
                
               
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
                <br>
                <label>{{ $t('frontend.jornada') }}: </label>
                <span>{{ rowData.jornadas }}</span>
                <div v-if="rowData.voucherUrl">
                  <label>{{ $t('backend.payment_file') }}: </label>
                  <a target="_blank" :href="'/'+rowData.voucherUrl"> {{ $t('backend.voucher') }}</a>
                  <span v-if="rowData.voucher_rechazado" class="label label-danger ml-1">Rechazado</span>
                  <br>
                  <button v-if="!rowData.voucher_rechazado && !rowData.pago"
                          type="button"
                          class="btn btn-xs btn-danger mt-1"
                          @click="mostrarRechazoModal">
                    <i class="fa fa-times"></i> Rechazar comprobante
                  </button>
                </div>

                <!-- Modal rechazo voucher -->
                <div v-if="showRechazoModal" class="rechazo-modal-backdrop" @click.self="showRechazoModal=false">
                  <div class="rechazo-modal">
                    <h5>Rechazar comprobante de pago</h5>
                    <p class="text-muted" style="font-size:.85rem;">
                      Se notificará al voluntario por mail y podrá subir un nuevo comprobante.
                    </p>
                    <div class="form-group">
                      <label style="font-size:.85rem;">Motivo (opcional)</label>
                      <textarea v-model="rechazoMotivo" class="form-control" rows="3"
                                placeholder="Ej: La imagen no es legible, falta el monto..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end" style="gap:.5rem;">
                      <button type="button" class="btn btn-default btn-sm" @click="showRechazoModal=false">Cancelar</button>
                      <button type="button" class="btn btn-danger btn-sm" :disabled="rechazando" @click="confirmarRechazo">
                        <span v-if="rechazando"><i class="fa fa-spinner fa-spin"></i> Enviando...</span>
                        <span v-else>Rechazar y notificar</span>
                      </button>
                    </div>
                    <div v-if="rechazoError" class="alert alert-danger mt-2" style="font-size:.8rem;">{{ rechazoError }}</div>
                  </div>
                </div>
            </div>
        </div>
        <div v-if="rowData.scholarship_requested" class="row mt-3">
            <div class="col-md-12">
                <div class="scholarship-box">
                    <div class="scholarship-box__header">
                        <i class="fa fa-graduation-cap"></i> Beca / Exención solicitada
                    </div>
                    <div class="scholarship-box__body">
                        <div v-if="rowData.scholarship_reason" class="scholarship-box__reason">
                            {{ rowData.scholarship_reason }}
                        </div>
                        <div v-if="rowData.scholarship_evidence_url" class="scholarship-box__evidence">
                            <a :href="'/'+rowData.scholarship_evidence_url" target="_blank">
                                <i class="fa fa-paperclip"></i> Ver documentación adjunta
                            </a>
                        </div>
                        <div v-if="!rowData.scholarship_reason && !rowData.scholarship_evidence_url" class="text-muted" style="font-size:.8rem;">
                            Sin motivo ni documentación adjunta.
                        </div>
                    </div>
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
    },
    tiposInscripcion: [],
  },
  data() {
    return {
      items: [],
      tiposInscripcionSinComillas: [],
      showRechazoModal: false,
      rechazoMotivo:    '',
      rechazando:       false,
      rechazoError:     null,
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
  mounted() {
    if(this.rowData.roles_aplicados){
        let cadenaJSONSinComillas = this.rowData.roles_aplicados.replace(/^"|"$/g, '');
        let cadenaJSONSinEscape = cadenaJSONSinComillas.replace(/\\\"/g, '"');
        this.items = JSON.parse(cadenaJSONSinEscape);
    }
    if(this.rowData.inscripciones_aplicadas){
      let cadenaJSONSinComillas = this.rowData.inscripciones_aplicadas.replace(/^"|"$/g, '');
      let cadenaJSONSinEscape = cadenaJSONSinComillas.replace(/\\\"/g, '"');
      this.tiposInscripcionSinComillas = JSON.parse(cadenaJSONSinEscape);
    }
  },
  methods: {
    onClick (event) {
      console.log('my-detail-row: on-click', event.target)
    },
    cargarAuditoria: function(id) {
      Event.$emit('vuetable-cargarAuditoria', id);
    },
    mostrarRechazoModal() {
      this.rechazoMotivo = '';
      this.rechazoError  = null;
      this.showRechazoModal = true;
    },
    confirmarRechazo() {
      this.rechazando  = true;
      this.rechazoError = null;
      const idActividad = this.rowData.idActividad;
      axios.post(
        '/admin/ajax/actividades/' + idActividad + '/inscripciones/rechazar/voucher',
        { idInscripcion: this.rowData.id, motivo: this.rechazoMotivo }
      ).then(() => {
        this.showRechazoModal = false;
        this.$set(this.rowData, 'voucher_rechazado', true);
        this.$set(this.rowData, 'voucher_rechazo_motivo', this.rechazoMotivo);
        Event.$emit('vuetable:refresh');
      }).catch(err => {
        this.rechazoError = 'Error al rechazar. Intentá nuevamente.';
        console.error(err);
      }).finally(() => {
        this.rechazando = false;
      });
    },
  },
}
</script>

<style scoped>
.rechazo-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.rechazo-modal {
    background: #fff;
    border-radius: 6px;
    padding: 1.5rem;
    width: 420px;
    max-width: 95vw;
    box-shadow: 0 4px 24px rgba(0,0,0,.2);
}
.scholarship-box {
    border: 1px solid #d1ecf1;
    border-left: 4px solid #17a2b8;
    border-radius: 4px;
    background: #f8fdfe;
    overflow: hidden;
    font-size: .85rem;
}
.scholarship-box__header {
    background: #e8f7fa;
    padding: 6px 12px;
    font-weight: 600;
    color: #0c5460;
    border-bottom: 1px solid #d1ecf1;
}
.scholarship-box__header .fa {
    margin-right: 5px;
}
.scholarship-box__body {
    padding: 10px 12px;
    color: #333;
}
.scholarship-box__reason {
    margin-bottom: 6px;
    white-space: pre-wrap;
    line-height: 1.5;
}
.scholarship-box__evidence a {
    font-size: .82rem;
    color: #17a2b8;
}
</style>
