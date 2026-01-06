<template>
    <div class="modal fade" id="rol-modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.assign') }} {{ $t('backend.role') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rol">{{ $t('backend.role') }}</label>
                    
                        <select
                            class="form-control"
                            v-model="rolSeleccionado"
                        >
                            <option
                                v-for="(label, key) in rolesFallback"
                                :key="key"
                                :value="key"
                            >
                                {{ label }}
                            </option>
                        </select>


                        <p class="help-block">
                            {{ $t('backend.only_one_tag_selectable_per_registration_selection') }}
                        </p>
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
        computed: {
            rolesFallback() {
                return this.$i18n.messages[this.$i18n.locale].backend.roles_actividad;
            }
        },
        watch: {
            rolesAplicado(newVal) {
                if (newVal.length > 0) {
                    this.rolSeleccionado = newVal[0].text;
                }
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
                Event.$emit('rol-asignado', this.rolSeleccionado);
            }
        }
    }
</script>

<style scoped>

</style>