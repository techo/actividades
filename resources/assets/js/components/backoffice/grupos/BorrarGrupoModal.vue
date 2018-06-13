<template>
    <div class="modal fade" id="borrarModal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Borrar Persona / Grupo</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p>
                                Las personas seleccionadas permanecerán inscriptas y sin asignar a ningun grupo
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                            type="button"
                            class="btn btn-default pull-left"
                            data-dismiss="modal"
                    >
                        Cerrar
                    </button>
                    <button
                            type="button"
                            class="btn btn-danger"
                            @click="this.confirmar"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "rol-modal",
        data(){
            return {
                roles: [],
                rolSeleccionado: ""
            }
        },
        created(){
            Event.$on('GruposToolbar:show-borrar-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                $('#borrarModal').modal('show');
            },
            axiosPost(url, fCallback, params = []) {
                this.loading = true;
                axios.post(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        this.loading = false;

                    })
                    .catch((error) => {
                        this.loading = false;
                        // Error
                        console.error('Error en el post: ' + url);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
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
            confirmar: function () {
                let idActividad = this.$root.$refs.miembrosTabla.moreParams.idActividad;
                let payload = { miembros: this.$parent.selected };
                let url = '/admin/ajax/actividades/' + idActividad + '/grupos/borrar';
                this.axiosPost(url, function(response, self) {
                    $('#rol-modal').modal('hide');
                    Event.$emit('vuetable-actualizarTabla');
                    Event.$emit('inscripciones-actualizar-tabla');
                }, payload);


            },
        }
    }
</script>

<style scoped>

</style>