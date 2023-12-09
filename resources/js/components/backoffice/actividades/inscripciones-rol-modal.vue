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
                        
                        <vue-tags-input
                            v-model="tag"
                            :tags="rolesAplicado"
                            placeholder=""
                            :autocomplete-items="rolesDisponibles"
                            @tags-changed="newTags => rolesAplicado = newTags"
                            :max-tags="1"
                        />
                        <p class="help-block">
                            Sólo 1 tag seleccionable por selección de inscripciones.
                        </p>
                        <input type="hidden"
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
    import VueTagsInput from '@johmun/vue-tags-input';
    export default {
        name: "inscripciones-rol-modal",
        components: { VueTagsInput },
        data(){
            return {
                rolSeleccionado: "",
                tag: "",
                rolesAplicado: [],
                rolesDisponibles: [],
                actividad: [],
            }
        },
        created(){
            Event.$on('show-rol-modal', this.mostrarModal);
        },
        methods: {
            mostrarModal: function () {
                this.axiosGet('/admin/ajax/actividades/' + this.$root.$refs.inscripcionestable.actividad,
                    function (data, self) {
                        self.actividad = data;
                        self.rolesDisponibles = self.actividad.roles_tags
                    });
                $('#rol-modal').modal('show');
            },
            confirmar: function () {
                $('#rol-modal').modal('hide');
                this.rolSeleccionado = this.rolesAplicado[0].text;
                Event.$emit('rol-asignado', this.rolSeleccionado);
            }
        }
    }
</script>

<style scoped>

</style>