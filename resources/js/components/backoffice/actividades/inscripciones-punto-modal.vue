<template>
    <div class="modal fade" id="punto-modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.assign') }} {{ $t('backend.meeting_point') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <h4>{{ $t('backend.email_will_be_sent_to_subscribers_notifying_them_of_this_change') }}</h4>
                        <div class="row">
                            <div class="col-md-12"  v-for="(punto, index) in puntos">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="punto" v-model="puntoSeleccionado" :value="punto.id" :checked="index == 0">
                                        {{punto.punto}}, {{punto.provincia}}, {{punto.pais}}
                                        ( {{punto.nombres}} {{punto.apellidoPaterno}})
                                    </label>
                                </div>
                            </div>
                        </div>
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
        name: "inscripciones-punto-modal",
        data(){
            return {
                puntoSeleccionado: "",
                puntos: []
            }
        },
        created(){
            Event.$on('show-punto-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                this.axiosGet('/admin/ajax/actividades/' + this.$root.$refs.inscripcionestable.actividad + '/puntos',
                    function (data, self) {
                        self.puntos = data;
                    });
                $('#punto-modal').modal('show');
            },
            confirmar: function () {
                $('#punto-modal').modal('hide');
                Event.$emit('punto-asignado', this.puntoSeleccionado);
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