<template>
    <div class="celda-beca">
        <!-- Aprobada -->
        <template v-if="aprobada">
            <span class="label label-success">{{ $t('backend.scholarship_approved') }}</span>
            <a v-if="rowData.scholarship_evidence_url"
               :href="'/' + rowData.scholarship_evidence_url" target="_blank" class="celda-beca__evidencia">
                <i class="fa fa-paperclip"></i> {{ $t('backend.view_evidence') }}
            </a>
        </template>

        <!-- Rechazada -->
        <template v-else-if="rechazada">
            <span class="label label-danger">{{ $t('backend.scholarship_rejected') }}</span>
            <a v-if="rowData.scholarship_evidence_url"
               :href="'/' + rowData.scholarship_evidence_url" target="_blank" class="celda-beca__evidencia">
                <i class="fa fa-paperclip"></i> {{ $t('backend.view_evidence') }}
            </a>
            <div v-if="rowData.scholarship_rejection_reason" class="celda-beca__motivo" :title="rowData.scholarship_rejection_reason">
                {{ rowData.scholarship_rejection_reason }}
            </div>
        </template>

        <!-- Solicitada (pendiente de resolución) -->
        <template v-else-if="rowData.scholarship_requested">
            <span class="label label-info">{{ $t('backend.requested') }}</span>
            <a v-if="rowData.scholarship_evidence_url"
               :href="'/' + rowData.scholarship_evidence_url" target="_blank" class="celda-beca__evidencia">
                <i class="fa fa-paperclip"></i> {{ $t('backend.view_evidence') }}
            </a>
            <div v-if="rowData.scholarship_reason" class="celda-beca__motivo" :title="rowData.scholarship_reason">
                {{ rowData.scholarship_reason }}
            </div>
            <div class="celda-beca__acciones">
                <button type="button" class="btn btn-xs btn-info" :disabled="procesando" @click.stop="aprobar">
                    <i class="fa fa-check"></i> {{ $t('backend.scholarship_approve') }}
                </button>
                <button type="button" class="btn btn-xs btn-danger" :disabled="procesando" @click.stop="abrirModal">
                    <i class="fa fa-times"></i> {{ $t('backend.scholarship_reject') }}
                </button>
            </div>
        </template>

        <span v-else class="text-muted">—</span>

        <!-- Modal de rechazo (mismo diseño que el de comprobante) -->
        <div v-if="showModal" class="beca-modal-backdrop" @click.self="cerrarModal">
            <div class="beca-modal" @click.stop>
                <h5>{{ $t('backend.scholarship_reject_title') }}</h5>
                <p class="text-muted" style="font-size: .85rem;">{{ $t('backend.scholarship_reject_help') }}</p>
                <div class="form-group">
                    <label style="font-size: .85rem;">{{ $t('backend.reason_optional') }}</label>
                    <textarea v-model="motivo" class="form-control" rows="3"
                              :placeholder="$t('backend.scholarship_reject_reason_placeholder')"></textarea>
                </div>
                <div class="beca-modal__actions">
                    <button type="button" class="btn btn-default btn-sm" @click="cerrarModal">{{ $t('backend.cancel') }}</button>
                    <button type="button" class="btn btn-danger btn-sm" :disabled="procesando" @click="rechazar">
                        <span v-if="procesando"><i class="fa fa-spinner fa-spin"></i> {{ $t('backend.sending') }}</span>
                        <span v-else>{{ $t('backend.reject_and_notify') }}</span>
                    </button>
                </div>
                <div v-if="error" class="alert alert-danger" style="font-size: .8rem; margin-top: 8px;">{{ error }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'celda-beca',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        fieldDef: { type: Object },
    },
    data() {
        return {
            showModal: false,
            motivo: '',
            procesando: false,
            error: null,
        }
    },
    computed: {
        // Aprobar la beca materializa la exención (exento_pago). scholarship_approved
        // es la marca explícita de la resolución desde el backoffice.
        aprobada() {
            return !!this.rowData.scholarship_approved
                || (!!this.rowData.exento_pago && !!this.rowData.scholarship_requested)
        },
        rechazada() {
            return !!this.rowData.scholarship_rejected && !this.aprobada
        },
        baseUrl() {
            return '/admin/ajax/actividades/' + this.rowData.idActividad + '/inscripciones'
        },
    },
    methods: {
        setCsrf() {
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
                document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        aprobar() {
            if (!window.confirm(this.$t('backend.scholarship_approve_confirm'))) return
            this.procesando = true
            this.error = null
            this.setCsrf()
            axios.post(this.baseUrl + '/aprobar/beca', { idInscripcion: this.rowData.id })
                .then(() => {
                    // Exención materializada → la columna Pago (mismo rowData) queda en verde "Exento".
                    this.$set(this.rowData, 'exento_pago', true)
                    this.$set(this.rowData, 'confirma', 1)
                    this.$set(this.rowData, 'scholarship_approved', true)
                    this.$set(this.rowData, 'scholarship_rejected', false)
                    this.procesando = false
                    Event.$emit('mensaje-success', { mensaje: this.$t('backend.scholarship_approved_ok') })
                })
                .catch(() => {
                    this.procesando = false
                    this.error = this.$t('backend.scholarship_action_error')
                    alert(this.$t('backend.scholarship_action_error'))
                })
        },
        abrirModal() {
            this.motivo = ''
            this.error = null
            this.showModal = true
        },
        cerrarModal() {
            this.showModal = false
        },
        rechazar() {
            this.procesando = true
            this.error = null
            this.setCsrf()
            axios.post(this.baseUrl + '/rechazar/beca', { idInscripcion: this.rowData.id, motivo: this.motivo })
                .then(() => {
                    this.$set(this.rowData, 'scholarship_rejected', true)
                    this.$set(this.rowData, 'scholarship_rejection_reason', this.motivo)
                    this.showModal = false
                    this.procesando = false
                    Event.$emit('mensaje-success', { mensaje: this.$t('backend.scholarship_rejected_ok') })
                })
                .catch(() => {
                    this.procesando = false
                    this.error = this.$t('backend.scholarship_action_error')
                })
        },
    },
}
</script>

<style scoped>
.celda-beca { min-width: 150px; }
.celda-beca .label { display: inline-block; }
.celda-beca__evidencia { display: inline-block; margin-left: 4px; white-space: nowrap; }
.celda-beca__motivo { font-size: .8rem; color: #666; margin-top: 3px; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.celda-beca__acciones { margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap; }
.beca-modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, .5); z-index: 1050; display: flex; align-items: center; justify-content: center; }
.beca-modal { background: #fff; border-radius: 4px; padding: 20px; width: 420px; max-width: 90%; box-shadow: 0 3px 12px rgba(0, 0, 0, .3); text-align: left; }
.beca-modal__actions { display: flex; justify-content: flex-end; gap: 8px; }
</style>
