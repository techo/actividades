<template>
    <div class="modal fade" id="grupo-modal" style="display: none;">
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
                                :options="dataGrupos"
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
                    <button type="button" class="btn btn-primary" @click="this.confirmar">{{ $t('backend.confirm') }}</button>
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
        name: "inscripciones-grupo-modal",
        data(){
            return {
                dataGrupos: [],
                grupoSeleccionado: undefined
            }
        },
        created(){
            Event.$on('show-grupo-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                $('#grupo-modal').modal('show');
                this.axiosGet('/admin/ajax/actividades/' + this.$root.$refs.inscripcionestable.actividad + '/grupos',
                function (data, self) {
                    self.dataGrupos = data;
                });
            },
            confirmar: function () {
                $('#grupo-modal').modal('hide');
                Event.$emit('grupo-asignado', this.grupoSeleccionado);
            },
            axiosGet(url, fCallback, params = []) {
                axios.get(url, params)
                    .then(response => {
                        fCallback(response.data, this)
                    })
                    .catch((error) => {
                        // Error
                        console.error('Error en: ' + url);
                        if (error.response) {
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
                        } else if (error.request) {
                            console.error(error.request);
                        } else {
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });

            },
        }
    }
</script>

<style scoped>

</style>