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

                <div class="voucher-drop-area"
                     :class="{ 'voucher-drop-area--hover': dragging }"
                     @click="selectFile"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="onDrop">

                    <div v-if="!fileName" class="d-flex flex-column align-items-center py-3">
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

                    <div v-if="fileName" class="d-flex flex-column align-items-center py-3">
                        <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                        <span class="text-muted small">{{ fileName }}</span>
                    </div>
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
.voucher-drop-area {
    border: 2px dashed #dee2e6;
    border-radius: .5rem;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    min-height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.voucher-drop-area:hover,
.voucher-drop-area--hover {
    border-color: #0d6efd;
    background: #f5f8ff;
}
</style>
