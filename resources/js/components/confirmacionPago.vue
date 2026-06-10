<template>
    <div>

        <!-- ── Subiendo: spinner ──────────────────────────────────── -->
        <div v-if="uploading" key="uploading"
             class="voucher-drop-area voucher-drop-area--uploading">
            <span class="spinner-border text-primary mb-2" style="width:2rem;height:2rem;" role="status"></span>
            <span class="text-muted" style="font-size:.9rem;">{{ $t('frontend.save') }}…</span>
        </div>

        <!-- ── Guardado: área azul con X ─────────────────────────── -->
        <div v-else-if="localVoucher" key="saved"
             class="voucher-saved-area"
             @click.stop="selectVoucher"
             :title="$t('frontend.voucher_click_to_browse')">
            <div class="d-flex align-items-center" style="min-width:0;">
                <i class="fas fa-check-circle mr-2" style="font-size:1.25rem;flex-shrink:0;"></i>
                <div style="min-width:0;">
                    <div class="font-weight-bold small text-truncate">{{ displayName }}</div>
                    <a :href="'/' + localVoucher" target="_blank"
                       class="small" style="opacity:.85;"
                       @click.stop>{{ $t('frontend.ver_adjunto') }}</a>
                </div>
            </div>
            <button type="button"
                    class="btn btn-sm ml-3"
                    style="flex-shrink:0;color:inherit;opacity:.75;"
                    @click.stop="limpiar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- ── Área vacía: drop zone ─────────────────────────────── -->
        <div v-else key="empty"
             class="voucher-drop-area"
             :class="{ 'voucher-drop-area--hover': dragging }"
             @click="selectVoucher"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="onDrop">
            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
            <span class="font-weight-bold text-muted" style="font-size:.9rem;">
                {{ $t('frontend.voucher_click_to_browse') }}
            </span>
            <span class="text-muted" style="font-size:.8rem;">
                {{ $t('frontend.voucher_drag_drop') }}
            </span>
            <span class="text-muted" style="font-size:.75rem;">
                {{ $t('frontend.voucher_formats') }}
            </span>
        </div>

        <input hidden type="file" accept=".jpg,.jpeg,.png,.pdf"
               ref="voucher_input" @change="onFileChange">

        <div v-if="errorMsg" class="text-danger small mt-1">{{ errorMsg }}</div>

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
            displayName:  this.voucher ? this.extractFilename(this.voucher) : null,
            uploading:    false,
            dragging:     false,
            errorMsg:     null,
        };
    },
    mounted() {
        if (this.localVoucher) this._notifyListo();
    },
    methods: {
        extractFilename(url) {
            if (!url) return '';
            return decodeURIComponent((url || '').split('/').pop());
        },
        selectVoucher() {
            this.$refs.voucher_input.value = '';
            this.$refs.voucher_input.click();
        },
        onDrop(event) {
            this.dragging = false;
            const f = event.dataTransfer && event.dataTransfer.files[0];
            if (f) this.upload(f);
        },
        onFileChange() {
            const f = this.$refs.voucher_input.files[0];
            if (f) this.upload(f);
        },
        limpiar() {
            // Borra el voucher del servidor y limpia el estado local
            axios.post('/ajax/inscripcion/clearVoucher', {
                idInscripcion: this.id,
            }, {
                headers: { 'X-CSRF-TOKEN': this.csrf_token },
            }).catch(() => {});  // fire-and-forget; si falla igual limpiamos UI

            this.localVoucher = null;
            this.displayName  = null;
            this.errorMsg     = null;
        },
        upload(file) {
            this.uploading = true;
            this.errorMsg  = null;
            const formData = new FormData();
            formData.append('voucher', file);
            formData.append('idInscripcion', this.id);
            axios.post('/ajax/inscripcion/voucherPago', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            }).then(response => {
                const url = response.data.voucherUrl || response.data.voucherURL || '';
                this.localVoucher = url;
                this.displayName  = this.extractFilename(url) || file.name;
                this._notifyListo();
            }).catch(() => {
                this.errorMsg = '⚠ Error al subir el archivo. Intentá de nuevo.';
            }).finally(() => {
                this.uploading = false;
            });
        },
        _notifyListo() {
            if (typeof window.notifyPagoListo === 'function') window.notifyPagoListo();
        },
    },
};
</script>

<style scoped>
/* Base compartida */
.voucher-drop-area,
.voucher-saved-area {
    border-radius: .5rem;
    min-height: 110px;
    width: 100%;
    box-sizing: border-box;
}

/* Drop area vacío */
.voucher-drop-area {
    border: 2px dashed #ced4da;
    background: #fafafa;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    gap: .25rem;
    transition: border-color .15s, background .15s;
}
.voucher-drop-area:hover,
.voucher-drop-area--hover {
    border-color: #0d6efd;
    background: #f0f5ff;
}
.voucher-drop-area--uploading {
    cursor: default;
    border-color: #adb5bd;
    background: #f8f9fa;
}

/* Guardado azul */
.voucher-saved-area {
    border: 2px solid #0d6efd;
    background: #e8f0fe;
    color: #0d6efd;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .75rem 1rem;
    transition: opacity .15s;
}
.voucher-saved-area:hover {
    opacity: .88;
}
</style>
