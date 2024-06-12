<template>
    <div class="modal fade" id="importar-modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.import_registrations_from_file') }}</h4>
                </div>
                <div class="modal-body">
                    <p>{{ $t('backend.to_register_volunteers_to_activity_from_file') }}
                        {{ $t('backend.it_must_follow_a_specific_format') }}
                        {{ $t('backend.example_from') }} <a href="/admin/inscripciones/importar/template" download>{{ $t('backend.here') }}</a></p>
                    <div class="form-group">
                        <label for="archivo">{{ $t('backend.select_file') }}</label>
                        <input type="file"
                               id="archivo"
                               name="archivo"
                               class="input-file"
                               @change="seleccionar($event.target.name, $event.target.files)"
                        >
                        <p class="text-error" v-show="uploadError">{{ $t('backend.select_only_one_file') }}</p>
                    </div>
                    <p>
                        <a href="/admin/logs/importar_inscripciones">{{ $t('backend.download_last_error_log') }}</a>
                    </p>
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
    export default {
        name: "inscripciones-importar-modal",
        data(){
            return {
                archivoSeleccionado: {},
                uploadError: false,
            }
        },
        created(){
            Event.$on('inscripciones:importar-button-clicked', this.mostrarModal);
        },
        methods: {
            seleccionar: function (nombre, archivos) {
                if(archivos.length > 1){
                    return this.uploadError = true;
                }
                this.uploadError = false;
                return this.archivoSeleccionado = archivos[0];

            },
            mostrarModal: function () {
                $('#importar-modal').modal('show');
            },
            confirmar: function () {
                $('#importar-modal').modal('hide');
                Event.$emit('inscripciones:archivo-seleccionado', this.archivoSeleccionado);
            }
        }
    }
</script>

<style scoped>

</style>