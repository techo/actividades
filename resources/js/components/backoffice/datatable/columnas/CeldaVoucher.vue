<template>
    <div class="celda-voucher">
        <template v-if="rowData.voucherUrl">
            <a target="_blank" :href="'/' + rowData.voucherUrl">
                <i class="fa fa-file"></i> {{ $t('backend.view_voucher') }}
            </a>
            <span v-if="rowData.voucher_rechazado" class="label label-danger">
                {{ $t('backend.rejected') }}
            </span>
            <div v-if="puedeRechazar">
                <button type="button" class="btn btn-xs btn-danger" @click.stop="abrirModal">
                    <i class="fa fa-times"></i> {{ $t('backend.reject_voucher') }}
                </button>
            </div>
        </template>
        <span v-else class="text-muted">—</span>

        <!-- Modal de rechazo -->
        <div v-if="showModal" class="voucher-modal-backdrop" @click.self="cerrarModal">
            <div class="voucher-modal" @click.stop>
                <h5>{{ $t('backend.reject_voucher_title') }}</h5>
                <p class="text-muted" style="font-size: .85rem;">
                    {{ $t('backend.reject_voucher_help') }}
                </p>
                <div class="form-group">
                    <label style="font-size: .85rem;">{{ $t('backend.reason_optional') }}</label>
                    <textarea v-model="motivo" class="form-control" rows="3"
                              :placeholder="$t('backend.reject_voucher_placeholder')"></textarea>
                </div>
                <div class="voucher-modal__actions">
                    <button type="button" class="btn btn-default btn-sm" @click="cerrarModal">
                        {{ $t('backend.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" :disabled="rechazando" @click="confirmar">
                        <span v-if="rechazando"><i class="fa fa-spinner fa-spin"></i> {{ $t('backend.sending') }}</span>
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
    name: 'celda-voucher',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        fieldDef: { type: Object },
    },
    data() {
        return {
            showModal: false,
            motivo: '',
            rechazando: false,
            error: null,
        }
    },
    computed: {
        // No se puede rechazar si ya está pago/confirmado o ya fue rechazado.
        puedeRechazar() {
            return !this.rowData.voucher_rechazado && !this.rowData.pago
        },
    },
    methods: {
        abrirModal() {
            this.motivo = ''
            this.error = null
            this.showModal = true
        },
        cerrarModal() {
            this.showModal = false
        },
        confirmar() {
            this.rechazando = true
            this.error = null
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
                document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            axios.post(
                '/admin/ajax/actividades/' + this.rowData.idActividad + '/inscripciones/rechazar/voucher',
                { idInscripcion: this.rowData.id, motivo: this.motivo }
            )
                .then(() => {
                    this.$set(this.rowData, 'voucher_rechazado', true)
                    this.$set(this.rowData, 'voucher_rechazo_motivo', this.motivo)
                    this.showModal = false
                    this.rechazando = false
                    Event.$emit('mensaje-success', { mensaje: this.$t('backend.voucher_rejected_ok') })
                })
                .catch(() => {
                    this.rechazando = false
                    this.error = this.$t('backend.error')
                })
        },
    },
}
</script>

<style scoped>
.celda-voucher {
    min-width: 110px;
}
.celda-voucher .label {
    margin-left: 4px;
}
.celda-voucher .btn-xs {
    margin-top: 4px;
}
.voucher-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, .5);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
}
.voucher-modal {
    background: #fff;
    border-radius: 4px;
    padding: 20px;
    width: 420px;
    max-width: 90%;
    box-shadow: 0 3px 12px rgba(0, 0, 0, .3);
    text-align: left;
}
.voucher-modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
</style>
