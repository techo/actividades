<template>
    <div class="modal fade" id="grupo-modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Asignar Grupo</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="grupo">Grupo</label>
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
        name: "inscripciones-grupo-modal",
        //props: ['grupos'],
        data(){
            return {
                dataGrupos: [],
                grupoSeleccionado: undefined
            }
        },
        created(){
            Event.$on('show-grupo-modal', this.mostrarModal);
            //this.dataGrupos = JSON.parse(this.grupos);
            this.dataGrupos = [
                {id: 6, nombre: 'Escuela 1'},
                {id: 7, nombre: 'Escuela 2'},
                {id: 3, nombre: 'Cuadrilla 1'},
                {id: 4, nombre: 'Cuadrilla 2'},
                {id: 5, nombre: 'Cuadrilla 3'},
                {id: 2, nombre: 'Barrio'},
                {id: 1, nombre: 'Actividad'}
            ];
        },
        methods: {
            mostrarModal: function () {
                $('#grupo-modal').modal('show');
            },
            confirmar: function () {
                $('#grupo-modal').modal('hide');
                Event.$emit('grupo-asignado', this.grupoSeleccionado);
            }
        }
    }
</script>

<style scoped>

</style>