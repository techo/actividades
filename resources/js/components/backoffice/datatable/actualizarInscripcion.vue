<template>
    <span>
        <button class="btn btn-sm btn-default" @click="this.actualizar">
            <i class="fa fa-spinner fa-spin" v-show="loadingIcon"></i>
            {{ $t('backend.update') }}
        </button>

        <i class="fa fa-check text-success" v-show="successIcon"></i>
        <i class="fa fa-exclamation text-danger" v-show="errorIcon"></i>
    </span>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "actualizar-inscripcion",
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        data() {
            return {
                estado: this.rowData.estado,
                pago: this.rowData.pago,
                presente: this.rowData.presente,
                loadingIcon: false,
                successIcon: false,
                errorIcon: false,
                idInscripcion: this.rowData.id,
                idActividad: this.rowData.idActividad,
            }
        },
        created() {
            Event.$on('error', this.error);
            Event.$on('success', this.success);
            Event.$on('pago_' + this.idInscripcion, this.actualizarPago);
            Event.$on('asistencia_' + this.idInscripcion, this.actualizarAsistencia);
            Event.$on('estado_' + this.idInscripcion, this.actualizarEstado);
        },
        watch: {
            estado: function () {
                this.loadingIcon = false;
                this.successIcon = false;
                this.errorIcon = false;
            },
            presente: function () {
                this.loadingIcon = false;
                this.successIcon = false;
                this.errorIcon = false;
            },
            pago: function () {
                this.loadingIcon = false;
                this.successIcon = false;
                this.errorIcon = false;
            },
        },
        methods: {
            actualizarPago(data) {
                this.pago = data;
            },
            actualizarAsistencia(data) {
                this.presente = data;
            },
            actualizarEstado(data) {
                this.estado = data;
            },
            error() {
                this.successIcon = false;
                this.loadingIcon = false;
                this.errorIcon = true;
            },
            success() {
                this.loadingIcon = false;
                this.errorIcon = false;
                this.successIcon = true;
            },
            actualizar() {
                let url = '/admin/ajax/actividades/' + this.idActividad + '/inscripciones/' + this.idInscripcion;
                let params = {
                    'pago': this.pago,
                    'presente': this.presente,
                    'estado': this.estado
                };
                this.loadingIcon = true;
                this.successIcon = false;
                this.errorIcon = false;
                this.axiosPost(url,
                    function () {
                        // se dispara el evento success en axiosPost
                    }, params);
            },
            axiosPost(url, fCallback, params = []) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.post(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        this.success();
                    })
                    .catch((error) => {
                        this.error();
                        // Error
                        console.info('Error en: ' + url);
                        console.error(error.response.status);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            // console.error(error.response.data);
                            // console.error(error.response.status);
                            // console.error(error.response.headers);
                            if (error.response.status === 422) {
                                // debugger;
                                this.validationErrors = Object.values(error.response.data);
                            }
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });

            },
        }
    }
</script>

<style scoped>
    i .fa-check {
        color: green
    }

    i .fa-exclamation {
        color: red
    }
</style>