<template>
    <div class="modal fade" id="grupoModal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.assign') }} {{ $t('backend.group') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="grupo">{{ $t('backend.group') }}</label>
                        <v-select
                                :options="grupos"
                                label="nombre"
                                placeholder="Seleccione"
                                name="grupo"
                                id="grupo"
                                v-model="grupoSeleccionado"
                                :filterable=true
                        >
                        </v-select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ $t('backend.close') }}</button>
                    <button type="button" class="btn btn-primary" @click.prevent="confirmar">{{ $t('backend.confirm') }}</button>
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
        name: "GruposModal",
        //props: ['grupos'],
        data(){
            return {
                grupos: [],
                grupoSeleccionado: undefined,
                idActividad: null
            }
        },
        created(){
            // this.idActividad = this.$root.$refs.miembrosTabla.moreParams.idActividad;
            Event.$on('GruposToolbar:show-grupo-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                let miembrosTabla = this.$root.$refs.miembrosTabla;
                this.idActividad = miembrosTabla.moreParams.idActividad;
                let url = '/admin/ajax/actividades/' + this.idActividad + '/grupos';
                this.axiosGet(url, function(result, self) {
                    self.grupos = result;
                    $('#grupoModal').modal('show');
                });
                //$('#rolModal').modal('show');

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
                let selected = this.$parent.selected;
                let payload = { miembros: selected, idGrupoDestino: this.grupoSeleccionado.idGrupo}
                let url = '/admin/ajax/actividades/' + this.idActividad + '/grupos/cambiar/grupo';
                this.axiosPost(url, function (result, self) {
                    Event.$emit('vuetable-actualizarTabla');
                    Event.$emit('inscripciones-actualizar-tabla');
                    self.$root.$refs.miembrosTabla.$refs.vuetableMiembros.selectedTo = [];
                    $('#grupoModal').modal('hide');
                }, payload);

                Event.$emit('GrupoModal:grupo-asignado', this.grupoSeleccionado);
            }
        }
    }
</script>

<style scoped>

</style>