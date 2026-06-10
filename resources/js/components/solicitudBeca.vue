<template>
    <div>
        <!-- Success screen -->
        <div v-if="enviado" class="text-center py-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                 style="background:#28a745;width:64px;height:64px;">
                <i class="fas fa-check" style="font-size:28px;color:white;"></i>
            </div>
            <h5 class="font-weight-bold mb-2">{{ $t('frontend.scholarship_success_title') }}</h5>
            <p class="text-muted" style="font-size:.9rem;">{{ $t('frontend.scholarship_success_subtitle') }}</p>
        </div>

        <!-- Form -->
        <div v-if="!enviado" class="row">

            <!-- Left column: reason textarea -->
            <div class="col-md-6 mb-4 mb-md-0">
                <p class="font-weight-bold mb-3">{{ $t('frontend.scholarship_panel_title') }}</p>
                <p class="text-muted mb-3" style="font-size:.9rem;">
                    {{ $t('frontend.scholarship_panel_description') }}
                </p>
                <textarea
                    v-model="reason"
                    class="form-control"
                    rows="6"
                    :placeholder="$t('frontend.scholarship_reason_placeholder')"
                    :class="{ 'is-invalid': error && !reason.trim() }"
                ></textarea>
                <div v-if="error && !reason.trim()" class="invalid-feedback d-block" style="font-size:.8rem;">
                    {{ $t('frontend.scholarship_reason_required') }}
                </div>
            </div>

            <!-- Right column: optional evidence upload -->
            <div class="col-md-6">
                <p class="font-weight-bold mb-3">{{ $t('frontend.scholarship_evidence_title') }}</p>

                <!-- Archivo seleccionado: área azul con X -->
                <div v-if="fileName" key="beca-saved"
                     class="beca-saved-area"
                     style="display:flex;align-items:center;justify-content:space-between;min-height:64px;padding:.75rem 1rem;"
                     @click.stop="selectFile"
                     :title="$t('frontend.voucher_click_to_browse')">
                    <div class="d-flex align-items-center" style="min-width:0;">
                        <i class="fas fa-check-circle mr-2" style="font-size:1.25rem;flex-shrink:0;"></i>
                        <div class="font-weight-bold small text-truncate">{{ fileName }}</div>
                    </div>
                    <button type="button"
                            class="btn btn-sm ml-3"
                            style="flex-shrink:0;color:inherit;opacity:.75;"
                            @click.stop="limpiarArchivo">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Drop zone vacío -->
                <div v-else key="beca-empty"
                     class="beca-drop-area"
                     :class="{ 'beca-drop-area--hover': dragging }"
                     style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;min-height:110px;padding:1rem;gap:.25rem;"
                     @click="selectFile"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="onDrop">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted" style="margin-bottom:.5rem;"></i>
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

                <input hidden type="file" accept=".jpg,.jpeg,.png,.pdf" ref="file_input" @change="onFileChange">
            </div>

        </div>

        <!-- Actions -->
        <div v-if="!enviado" class="d-flex justify-content-end mt-4 pt-3 border-top" style="gap:.5rem;">
            <button type="button" class="btn btn-outline-secondary" @click="goBack">
                {{ $t('frontend.go_back') }}
            </button>
            <button type="button" class="btn btn-primary" @click="submit" :disabled="enviando">
                <span v-if="enviando" class="spinner-border spinner-border-sm mr-1" role="status"></span>
                {{ $t('frontend.scholarship_submit_btn') }}
            </button>
        </div>

        <div v-if="errorServidor" class="alert alert-danger mt-3" style="font-size:.85rem;">
            {{ errorServidor }}
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    name: 'solicitudBeca',
    props: ['id', 'csrf_token'],
    data() {
        return {
            reason:       '',
            file:         null,
            fileName:     null,
            dragging:     false,
            enviando:     false,
            enviado:      false,
            error:        false,
            errorServidor: null,
        };
    },
    methods: {
        selectFile() {
            this.$refs.file_input.value = '';
            this.$refs.file_input.click();
        },
        onFileChange() {
            const f = this.$refs.file_input.files[0];
            if (f) { this.file = f; this.fileName = f.name; }
        },
        onDrop(event) {
            this.dragging = false;
            const f = event.dataTransfer.files[0];
            if (f) { this.file = f; this.fileName = f.name; }
        },
        limpiarArchivo() {
            this.file     = null;
            this.fileName = null;
        },
        goBack() {
            if (typeof window.becaGoBack === 'function') window.becaGoBack();
        },
        submit() {
            this.error = false;
            this.errorServidor = null;

            if (!this.reason.trim()) {
                this.error = true;
                return;
            }

            this.enviando = true;

            const formData = new FormData();
            formData.append('idInscripcion', this.id);
            formData.append('reason', this.reason.trim());
            if (this.file) {
                formData.append('evidence', this.file);
            }

            axios.post('/ajax/inscripcion/becaSolicitud', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-CSRF-TOKEN': this.csrf_token,
                },
            })
            .then(() => {
                this.enviado = true;
                this.$emit('submitted');
                if (typeof window.notifyPagoListo === 'function') window.notifyPagoListo();
            })
            .catch(err => {
                const msg = err.response && err.response.data && err.response.data.message
                    ? err.response.data.message
                    : 'Error al enviar la solicitud.';
                this.errorServidor = msg;
            })
            .finally(() => {
                this.enviando = false;
            });
        },
    },
};
</script>

<style scoped>
/* Drop zone vacío */
.beca-drop-area {
    border: 2px dashed #ced4da;
    background: #fafafa;
    border-radius: .5rem;
    cursor: pointer;
    min-height: 110px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    gap: .25rem;
    transition: border-color .15s, background .15s;
}
.beca-drop-area:hover,
.beca-drop-area--hover {
    border-color: #0d6efd;
    background: #f0f5ff;
}

/* Archivo seleccionado (azul) */
.beca-saved-area {
    border: 2px solid #0d6efd;
    background: #e8f0fe;
    color: #0d6efd;
    border-radius: .5rem;
    cursor: pointer;
    min-height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .75rem 1rem;
    transition: opacity .15s;
}
.beca-saved-area:hover {
    opacity: .88;
}
</style>
