<template>
    <div class="celda-documento">
        <template v-if="rowData.documento_frente || rowData.documento_dorso">
            <a v-if="rowData.documento_frente" target="_blank" :href="'/' + rowData.documento_frente">
                <i class="fa fa-id-card"></i> {{ $t('backend.front') }}
            </a>
            <a v-if="rowData.documento_dorso" target="_blank" :href="'/' + rowData.documento_dorso">
                <i class="fa fa-id-card-o"></i> {{ $t('backend.back') }}
            </a>
            <span v-if="rechazado" class="label label-danger">{{ $t('backend.rejected') }}</span>
            <div v-if="!rechazado">
                <button type="button" class="btn btn-xs btn-danger" @click.stop="abrirModal">
                    <i class="fa fa-times"></i> {{ $t('backend.reject_document') }}
                </button>
            </div>
        </template>
        <span v-else class="text-muted">—</span>

        <!-- Modal de rechazo -->
        <div v-if="showModal" class="doc-modal-backdrop" @click.self="cerrarModal">
            <div class="doc-modal" @click.stop>
                <h5>{{ $t('backend.reject_document_title') }}</h5>
                <p class="text-muted" style="font-size: .85rem;">
                    {{ $t('backend.reject_document_help') }}
                </p>
                <div class="form-group">
                    <label style="font-size: .85rem;">{{ $t('backend.reason_optional') }}</label>
                    <textarea v-model="motivo" class="form-control" rows="3"
                              :placeholder="$t('backend.reject_document_placeholder')"></textarea>
                </div>
                <div class="doc-modal__actions">
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
    name: 'celda-documento',
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
        rechazado() {
            return !!this.rowData.documento_rechazado &&
                this.rowData.documento_rechazado !== '0'
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
                '/admin/ajax/actividades/' + this.rowData.idActividad + '/inscripciones/rechazar/documento',
                { idInscripcion: this.rowData.id, motivo: this.motivo }
            )
                .then(() => {
                    this.$set(this.rowData, 'documento_rechazado', true)
                    this.$set(this.rowData, 'documento_rechazo_motivo', this.motivo)
                    this.showModal = false
                    this.rechazando = false
                    Event.$emit('mensaje-success', { mensaje: this.$t('backend.document_rejected_ok') })
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
.celda-documento {
    min-width: 130px;
}
.celda-documento a {
    margin-right: 6px;
    white-space: nowrap;
}
.celda-documento .label {
    margin-left: 2px;
}
.celda-documento .btn-xs {
    margin-top: 4px;
}
.doc-modal-backdrop {
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
.doc-modal {
    background: #fff;
    border-radius: 4px;
    padding: 20px;
    width: 420px;
    max-width: 90%;
    box-shadow: 0 3px 12px rgba(0, 0, 0, .3);
    text-align: left;
}
.doc-modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
</style>
