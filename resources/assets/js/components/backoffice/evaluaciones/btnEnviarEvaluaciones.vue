<template>
    <span>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalConfirmar">
            <i class="fa fa-paper-plane"></i> Enviar Evaluaciones
        </button>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalConfirmar">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Confirmar Envío de Evaluaciones</h4>
                    </div>
                    <div class="modal-body">
                        <span v-if="!error && !success">
                            <p>Al hacer click en <strong>Enviar</strong> se enviará un correo electrónico a todos los inscriptos en {{ actividad.nombreActividad }}.</p>
                            <p>
                                <b>El mail solo le llega a los inscriptos que están marcados como *PRESENTE*.</b>
                            </p>
                            <p>También podés <strong>Copiar el link</strong> a las evaluaciones para enviar por otros medios.</p>
                            <input type="text" id="data-url-evaluaciones" tabindex="-1" aria-hidden="true" :value="urlEvaluaciones">
                        </span>
                        <span v-if="success">
                            <strong>
                                ¡Felicidades! Los correos ya van en camino.
                            </strong>
                        </span>
                        <span v-if="error">
                            <p class="text-error">¡Ocurrió un error al enviar los correos! intentalo de nuevo o
                                contacta al administrador del sistema para más información.</p>
                        </span>
                    </div>
                  <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                            <i class="fa fa-ban"></i>
                            Cerrar
                        </button>
                        <button v-bind:class="getClass(copiarClicked)"
                             type="button"
                             @click="copiarClipboard">
                            <i class="fa fa-clipboard"></i>
                            &nbsp {{ mensajeCopiar }}
                        </button>
                        <button type="button" class="btn btn-primary" @click="enviarEvaluaciones" v-if="!loading">
                            <i class="fa fa-paper-plane"></i>
                            Enviar
                        </button>
                        <button type="button" class="btn btn-default" v-else>
                            <i class="fa fa-spinner fa-spin fa-fw"></i>
                            <span class="sr-only">Loading...</span> Espera
                        </button>
                  </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </span>
</template>

<script>
import axios from 'axios';
import store from '../stores/store';

export default {
    name: "btnEnviarEvaluaciones",
    props: ['prop-actividad'],
    data: function () {
        return {
            loading: false,
            success: false,
            error: false,
            actividad: {},
            mensajeCopiar: "Copiar link",
            copiarClicked: false,
            urlEvaluaciones: window.location.origin + '/actividades/' + store.state.idActividad + '/evaluaciones'
        }
    },
    created: function () {
        this.actividad = this.propActividad;
    },
    methods: {
        enviarEvaluaciones: function () {
            this.loading = true;
            this.error = false;
            let url = '/admin/ajax/actividades/'+ this.actividad.idActividad +'/enviar-evaluaciones';
            let payload = {
                actividad: this.actividad
            };
            this.axiosPost(url, function (response, self) {
                self.loading = false;
                self.error = false;
                self.success = true;
            }, payload);
        },
        getClass: function (clicked) {
            let btnClass = clicked ? 'btn-success' : 'btn-primary';
            return {
                'btn': true,
                [btnClass]: true
            };
        },
        copiarClipboard: function () {
            let url = document.getElementById('data-url-evaluaciones');
            url.select();
            document.execCommand("copy");
            this.copiarClicked = true;
            this.mensajeCopiar = "Copiado!";
        },
        axiosPost(url, fCallback, params = []) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.post(url, params)
                .then(response => {
                    fCallback(response.data, this);
                    //Event.$emit('success');
                    this.readonly = true;
                })
                .catch((error) => {
                    this.loading = false;
                    this.error = true;
                    //Event.$emit('error');
                    // Error
                    console.info('Error en: ' + url);
                    console.error(error.response.status);
                    if (error.response) {
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
    .text-error {
        color: red;
    }
    #data-url-evaluaciones {
        position: absolute;
        left: -9999px;
    }
</style>