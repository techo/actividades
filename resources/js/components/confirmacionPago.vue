<template>
    <div>
        <!-- Upload area -->
        <div class="voucher-drop-area"
             :class="{ 'voucher-drop-area--hover': dragging }"
             @click="selectVoucher"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="onDrop">

            <div v-if="!nombre_voucher && !voucher" class="text-center py-3">
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                <span class="d-block font-weight-bold text-muted" style="font-size:.9rem;">
                    {{ $t('frontend.voucher_click_to_browse') }}
                </span>
                <span class="d-block text-muted mt-1" style="font-size:.8rem;">
                    {{ $t('frontend.voucher_drag_drop') }}
                </span>
                <span class="d-block text-muted mt-1" style="font-size:.75rem;">
                    {{ $t('frontend.voucher_formats') }}
                </span>
            </div>

            <div v-if="voucher && !nombre_voucher" class="text-center py-3">
                <i class="fas fa-file-alt fa-2x text-success mb-2 d-block"></i>
                <span class="d-block text-muted small">{{ $t('frontend.data_uploaded') }}</span>
                <a v-if="voucher" :href="'/' + voucher" target="_blank"
                   class="d-block small mt-1"
                   @click.stop>
                    {{ $t('frontend.ver_adjunto') }}
                </a>
            </div>

            <div v-if="nombre_voucher" class="text-center py-3">
                <i class="fas fa-file-alt fa-2x text-primary mb-2 d-block"></i>
                <span class="d-block text-muted small">{{ nombre_voucher }}</span>
            </div>

        </div>

        <input hidden type="file" accept=".jpg,.jpeg,.png,.pdf" @change="guardar_voucher" ref="voucher_input">

        <!-- Save button -->
        <div v-if="nombre_voucher" class="mt-2">
            <button type="button" class="btn btn-primary w-100" @click="submitVoucher">
                {{ $t('frontend.save') }}
            </button>
        </div>

        <!-- Success state -->
        <div v-if="guardo" class="mt-2">
            <button type="button" class="btn btn-success w-100" disabled>
                <i class="fas fa-check mr-1"></i>{{ $t('frontend.changes_success') }}
            </button>
        </div>

        <!-- Confirm later -->
        <div v-if="!guardo && !nombre_voucher" class="mt-2">
            <button type="button" class="btn btn-link w-100 text-muted p-0" @click="redirectToIndex">
                {{ $t('frontend.confirm_later') }}
            </button>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "confirmacionPago",
        props: ['id', 'csrf_token', 'voucher'],
        data: function () {
            return {
                nombre_voucher: null,
                new_voucher:    null,
                voucherUrl:     null,
                guardo:         false,
                dragging:       false,
            };
        },
        methods: {
            onDrop(event) {
                this.dragging = false;
                const files = event.dataTransfer.files;
                if (files && files[0]) {
                    this.new_voucher    = files[0];
                    this.nombre_voucher = files[0].name;
                }
            },
            guardar_voucher(event) {
                this.new_voucher    = this.$refs.voucher_input.files[0];
                this.nombre_voucher = this.$refs.voucher_input.files[0].name;
            },
            selectVoucher() {
                this.$refs.voucher_input.click();
            },
            submitVoucher() {
                const formData = new FormData();
                formData.append('voucher', this.new_voucher);
                formData.append('idInscripcion', this.id);
                const headers = { 'Content-Type': 'multipart/form-data' };
                axios.post('/ajax/inscripcion/voucherPago', formData, { headers })
                    .then(response => {
                        this.guardo         = true;
                        this.nombre_voucher = null;
                        this.voucher        = response.data.voucherURL;
                    })
                    .catch(() => {});
            },
            redirectToIndex() {
                window.location.href = '/';
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
