<template>
    <div>

        <!-- ── Estado: voucher guardado en DB (área azul) ─────────── -->
        <div v-if="localVoucher && !pendingName"
             class="voucher-saved-area d-flex align-items-center justify-content-between p-3"
             @click.stop="selectVoucher"
             title="Hacer clic para cambiar el archivo">
            <div class="d-flex align-items-center" style="min-width:0;">
                <i class="fas fa-check-circle mr-2" style="font-size:1.25rem;flex-shrink:0;"></i>
                <div style="min-width:0;">
                    <div class="font-weight-bold small text-truncate">{{ extractFilename(localVoucher) }}</div>
                    <a :href="'/' + localVoucher" target="_blank"
                       class="small" style="opacity:.85;"
                       @click.stop>{{ $t('frontend.ver_adjunto') }}</a>
                </div>
            </div>
            <button type="button" class="btn btn-sm ml-2" style="flex-shrink:0;opacity:.8;color:inherit;"
                    @click.stop="limpiar"
                    :title="$t('frontend.save')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- ── Estado: archivo seleccionado pero no guardado ─────── -->
        <div v-if="pendingName"
             class="voucher-pending-area d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center" style="min-width:0;">
                <i class="fas fa-file-alt text-primary mr-2" style="font-size:1.25rem;flex-shrink:0;"></i>
                <span class="small font-weight-bold text-truncate">{{ pendingName }}</span>
            </div>
            <button type="button" class="btn btn-sm ml-2" style="flex-shrink:0;"
                    @click.stop="limpiarPendiente">
                <i class="fas fa-times text-muted"></i>
            </button>
        </div>

        <!-- ── Estado vacío: drop area ───────────────────────────── -->
        <div v-if="!localVoucher && !pendingName"
             class="voucher-drop-area d-flex flex-column align-items-center justify-content-center"
             :class="{ 'voucher-drop-area--hover': dragging }"
             @click="selectVoucher"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="onDrop">
            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
            <span class="font-weight-bold text-muted" style="font-size:.9rem;">
                {{ $t('frontend.voucher_click_to_browse') }}
            </span>
            <span class="text-muted mt-1" style="font-size:.8rem;">
                {{ $t('frontend.voucher_drag_drop') }}
            </span>
            <span class="text-muted mt-1" style="font-size:.75rem;">
                {{ $t('frontend.voucher_formats') }}
            </span>
        </div>

        <input hidden type="file" accept=".jpg,.jpeg,.png,.pdf"
               ref="voucher_input" @change="onFileChange">

        <!-- ── Botón guardar (solo cuando hay archivo pendiente) ─── -->
        <div v-if="pendingName" class="mt-2">
            <button type="button" class="btn btn-primary w-100"
                    @click="submitVoucher" :disabled="uploading">
                <span v-if="uploading">
                    <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                    {{ $t('frontend.save') }}…
                </span>
                <span v-else>{{ $t('frontend.save') }}</span>
            </button>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
export default {
    name: 'confirmacionPago',
    props: ['id', 'csrf_token', 'voucher'],
    data() {
        return {
            localVoucher: this.voucher || null,
            pendingName:  null,
            pendingFile:  null,
            uploading:    false,
            dragging:     false,
        };
    },
    mounted() {
        // Si ya hay voucher en DB al cargar la página, habilitar Finalizar
        if (this.localVoucher) {
            this._notifyListo();
        }
    },
    methods: {
        extractFilename(url) {
            if (!url) return '';
            return decodeURIComponent(url.split('/').pop());
        },
        onDrop(event) {
            this.dragging = false;
            const f = event.dataTransfer.files[0];
            if (f) { this.pendingFile = f; this.pendingName = f.name; }
        },
        onFileChange() {
            const f = this.$refs.voucher_input.files[0];
            if (f) { this.pendingFile = f; this.pendingName = f.name; }
        },
        selectVoucher() {
            this.$refs.voucher_input.click();
        },
        limpiar() {
            // Borra voucher local → vuelve al área vacía (sin borrar en DB)
            this.localVoucher = null;
            this.pendingName  = null;
            this.pendingFile  = null;
        },
        limpiarPendiente() {
            this.pendingName = null;
            this.pendingFile = null;
        },
        submitVoucher() {
            if (!this.pendingFile) return;
            this.uploading = true;
            const formData = new FormData();
            formData.append('voucher', this.pendingFile);
            formData.append('idInscripcion', this.id);
            axios.post('/ajax/inscripcion/voucherPago', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            }).then(response => {
                // El endpoint devuelve el modelo Inscripcion serializado
                this.localVoucher = response.data.voucherUrl
                                 || response.data.voucherURL
                                 || '';
                this.pendingName  = null;
                this.pendingFile  = null;
                this._notifyListo();
            }).catch(() => {
            }).finally(() => {
                this.uploading = false;
            });
        },
        _notifyListo() {
            if (typeof window.notifyPagoListo === 'function') {
                window.notifyPagoListo();
            }
        },
    },
};
</script>

<style scoped>
/* ── Drop area vacío ── */
.voucher-drop-area {
    border: 2px dashed #dee2e6;
    border-radius: .5rem;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    min-height: 130px;
}
.voucher-drop-area:hover,
.voucher-drop-area--hover {
    border-color: #0d6efd;
    background: #f5f8ff;
}

/* ── Archivo guardado en DB (azul) ── */
.voucher-saved-area {
    border: 2px solid #0d6efd;
    border-radius: .5rem;
    background: #e8f0fe;
    color: #0d6efd;
    cursor: pointer;
    min-height: 64px;
    transition: opacity .15s;
}
.voucher-saved-area:hover {
    opacity: .85;
}

/* ── Archivo seleccionado pendiente de guardar ── */
.voucher-pending-area {
    border: 2px dashed #6c757d;
    border-radius: .5rem;
    background: #f8f9fa;
    min-height: 64px;
}
</style>
