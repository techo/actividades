<template>
    <div class="modal fade" id="importar-modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Importar inscripciones desde archivo</h4>
                </div>
                <div class="modal-body">
                    <p>Para inscribir voluntarios a la actividad desde un archivo,
                        el mismo deberá respetar un formato específico. Puede descargar el archivo
                        de ejemplo desde <a href="#">aquí</a></p>
                    <div class="form-group">
                        <label for="archivo">Seleccionar archivo</label>
                        <input type="file"
                               id="archivo"
                               name="archivo"
                               class="input-file"
                               @change="seleccionar($event.target.name, $event.target.files)"
                        >
                        <p class="text-error" v-show="uploadError">Seleccionar solo un archivo</p>
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