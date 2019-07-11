<template>
    <span>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalConfirmar">
            <i class="fa fa-paper-plane"></i> Enviar Evaluaciones
        </button>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalConfirmar">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            @click="success = false; error= false;">
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
                                Se enviaron notificaciones a {{ notificados }} inscriptos.
                            </strong>
                        </span>
                        <span v-if="error">
                            <p class="text-error">¡Ocurrió un error al enviar los correos! intentalo de nuevo o
                                contacta al administrador del sistema para más información.</p>
                        </span>
                    </div>
                  <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"
                            @click="success = false; error= false;">
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
            urlEvaluaciones: window.location.origin + '/actividades/' + store.state.idActividad + '/evaluaciones',
            notificados: 0,
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
            axios.post(url, payload)
                .then(response => {
                    this.loading = false;
                    this.error = false;
                    this.success = true;
                    this.notificados = response.data;
                    this.readonly = true;
                })
                .catch((error) => {
                    this.loading = false;
                    this.error = true;
                });
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
        }
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