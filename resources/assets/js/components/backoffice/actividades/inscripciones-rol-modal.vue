<template>
    <div class="modal fade" id="rol-modal" style="display: none;">
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
    export default {
        name: "inscripciones-rol-modal",
        data(){
            return {
                rolSeleccionado: ""
            }
        },
        created(){
            Event.$on('show-rol-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                $('#rol-modal').modal('show');
            },
            confirmar: function () {
                $('#rol-modal').modal('hide');
                Event.$emit('rol-asignado', this.rolSeleccionado);
            }
        }
    }
</script>

<style scoped>

</style>