<template>
    <div class="modal fade" id="rolModal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Asignar Rol</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <input type="text"
                               id="rol"
                               name="rol"
                               class="input form-control"
                               placeholder="Escriba el rol"
                               v-model="rolSeleccionado"
                        >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" @click="this.confirmar">Confirmar</button>
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
            Event.$on('GruposToolbar:show-rol-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                let idActividad = this.$root.$refs.miembrosTabla.moreParams.idActividad;
                let url = '/admin/ajax/actividades/' + idActividad + '/grupos';
                this.axiosGet(url, function(result, self) {
                    self.roles = result;
                    $('#rolModal').modal('show');
                });
            },
            axiosGet(url, fCallback, params = []) {
                this.loading = true;
                axios.get(url, params)
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
                let payload = { miembros: this.$parent.selected, rol: this.rolSeleccionado};
                let idActividad = this.$root.$refs.miembrosTabla.moreParams.idActividad;
                let url = '/admin/ajax/actividades/' + idActividad + '/grupos/cambiar/rol';
                this.axiosPost(url, function (result, self) {
                    Event.$emit('vuetable-actualizarTabla');
                    self.$root.$refs.miembrosTabla.$refs.vuetableMiembros.selectedTo = [];
                    $('#rolModal').modal('hide');
                }, payload);

                Event.$emit('RolModal:rol-asignado');
            },
        }
    }
</script>

<style scoped>

</style>