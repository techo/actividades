<template>
    <div class="modal fade" id="grupoEditModal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.edit') }} {{ $t('backend.evaluation_link') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="grupo">{{ $t('backend.evaluation_link') }}</label>
                        <input type="text"
                               id="rol"
                               name="rol"
                               class="input form-control"
                               placeholder="Escriba link"
                               v-model="linkSeleccionado"
                        />
                        <small class="form-text text-danger">{{ textoError }}&nbsp;<br> </small>
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
        name: "grupo-edit-modal",
        //props: ['grupos'],
        data(){
            return {
                linkSeleccionado: undefined,
                grupos: [],
                idActividad: null,
                textoError: "",
            }
        },
        created(){
            // this.idActividad = this.$root.$refs.miembrosTabla.moreParams.idActividad;
            Event.$on('GruposToolbar:show-grupo-edit-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                let miembrosTabla = this.$root.$refs.miembrosTabla;
                this.idActividad = miembrosTabla.moreParams.idActividad;
                this.grupos = this.$parent.selected;
                if (this.grupos.length==1)
                    this.linkSeleccionado = this.grupos[0].linkEvaluacion
                $('#grupoEditModal').modal('show');
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
                        this.errors = error;
                        this.loading = false;
                        // Error
                        var errors = error.response.data.errors
                        for (var prop in errors) {
                            this.textoError = errors[prop][0];
                        }
                    });
            },
            confirmar: function () {
                let selected = this.$parent.selected;
                let payload = { miembros: selected, linkSeleccionado: this.linkSeleccionado}
                let url = '/admin/ajax/actividades/' + this.idActividad + '/grupos/cambiar/link';
                this.axiosPost(url, function (result, self) {
                    Event.$emit('vuetable-actualizarTabla');
                    Event.$emit('inscripciones-actualizar-tabla');
                    self.$root.$refs.miembrosTabla.$refs.vuetableMiembros.selectedTo = [];
                    $('#grupoEditModal').modal('hide');
                }, payload);

                Event.$emit('GrupoModal:grupo-asignado', this.grupoSeleccionado);
            }
        }
    }
</script>

<style scoped>

</style>