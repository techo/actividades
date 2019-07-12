<template>
    <div class="modal fade" id="desinscribir-modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Desinscribir</h4>
                </div>
                <div class="modal-body">
                    <p>Se van a eliminar las inscripciones seleccionadas de esta actividad ¿está SEGURO?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" @click="this.confirmar">Desinscribir</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</template>

<script>
    export default {
        name: "inscripciones-desinscribir-modal",
        data(){
            return {
                confirmacion: 0
            }
        },
        created(){
            Event.$on('show-desinscribir-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                $('#desinscribir-modal').modal('show');
            },
            confirmar: function () {
                $('#desinscribir-modal').modal('hide');
                Event.$emit('desinscripto');
            },
        }
    }
</script>

<style scoped>

</style>