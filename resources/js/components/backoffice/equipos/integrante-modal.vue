<template>
    <div :class="{ 'modal': true, 'fade': true }" :style="{}" id="inscribir-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.new_member') }}</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idIntegrante" class="callout callout-danger">
                        <p v-text="errors.idIntegrante[0]"></p>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idPersona }">
                                <label for="idPersona">{{ $t('backend.person') }}</label>
                                <v-select :options="personas" @search="onSearch" label="nombre" v-model="persona"
                                    :filterable="false" :selectOnTab="true"></v-select>
                                <span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                                <label for="rol">{{ $t('backend.role') }}</label>
                                <input v-model="form.rol" name="rol" type="text" class="form-control" required>
                                <span v-if="errors.rol" v-text="errors.rol[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.estado }">
                                <label for="estado">{{ $t('backend.state') }}</label>
                                <select v-model="form.estado" name="estado" class="form-control" required>
                                    <option value="1" :selected="form.estado == 1">{{ $t('backend.active') }}</option>
                                    <option value="0" :selected="form.estado == 0">{{ $t('backend.inactive') }}</option>
                                </select>
                                <span v-if="errors.estado" v-text="errors.estado[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.despliegue }">
                                <label for="despliegue">{{ $t('backend.deployment') }}</label>
                                <select v-model="form.despliegue" name="despliegue" class="form-control" required>
                                    <option value="Oficina" :selected="form.despliegue == 'Oficina'">{{ $t('backend.office') }}</option>
                                    <option value="Comunidad" :selected="form.despliegue == 'Comunidad'">{{ $t('backend.community') }}</option>
                                    <option value="Otras" :selected="form.despliegue == 'Otras'">{{ $t('backend.other') }}</option>
                                </select>
                                <span v-if="errors.despliegue" v-text="errors.despliegue[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.relacion }">
                                <label for="relacion">{{ $t('backend.relationship') }}</label>
                                <select v-model="form.relacion" name="relacion" class="form-control" required>
                                    <option value="Rentado" :selected="form.relacion == 'Rentado'">{{ $t('backend.rented') }}</option>
                                    <option value="Voluntario" :selected="form.relacion == 'Voluntario'">{{ $t('backend.volunteer') }}</option>
                                    <option value="Pasante" :selected="form.relacion == 'Pasante'">{{ $t('backend.intern') }}</option>
                                </select>
                                <span v-if="errors.relacion" v-text="errors.relacion[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.fechaInicio }">
                                <label for="fechaInicio">{{ $t('backend.start_date') }}</label>
                                <input v-model="form.fechaInicio" name="fechaInicio" type="date" class="form-control"
                                    required>
                                <span v-if="errors.fechaInicio" v-text="errors.fechaInicio[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.fechaFin }">
                                <label for="fechaFin">{{ $t('backend.end_date') }}</label>
                                <input v-model="form.fechaFin" name="fechaFin" type="date" class="form-control" required>
                                <span v-if="errors.fechaFin" v-text="errors.fechaFin[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.archivo_carta_compromiso }">
                                <label for="archivo_carta_compromiso">{{ $t('backend.commitment_letter') }}</label>
                                <a v-if="form.archivo_carta_compromiso != null" :href="'/'+form.archivo_carta_compromiso" target="_blank"> {{ $t('backend.view_uploaded_letter') }}</a>
                                <input type="file" hidden  style="display: none;" ref="archivo_carta_compromiso"  @change="guardarCartaCompromiso">

                                <button  class="btn btn-light" @click="selectCarta" ><i class="fa fa-edit"></i></button>
                                {{ this.nombre_carta_compromiso }}
                                <span v-if="errors.archivo_carta_compromiso" v-text="errors.archivo_carta_compromiso[0]" class="help-block"></span>
                            </div>
                        </div>
                        <!-- <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.archivo_plan_de_trabajo }">
                                <label for="archivo_plan_de_trabajo">Plan de Trabajo</label>
                                <a v-if="form.archivo_plan_de_trabajo != null" :href="'/'+form.archivo_plan_de_trabajo" target="_blank"> Ver Plan Cargado</a>
                                <input ref="archivo_plan_de_trabajo" type="file" class="form-control">
                                <span v-if="errors.archivo_plan_de_trabajo" v-text="errors.archivo_plan_de_trabajo[0]"
                                    class="help-block"></span>
                            </div>
                        </div> -->
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.descripcion_rol }">
                                <label for="descripcion_rol">{{ $t('backend.role_description') }}</label>
                                <input v-model="form.descripcion_rol" name="descripcion_rol" type="text" class="form-control"
                                    required>
                                <span v-if="errors.descripcion_rol" v-text="errors.descripcion_rol[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.meta }">
                                <label for="meta">{{ $t('backend.goal') }}</label>
                                <input v-model="form.meta" name="meta" type="text" class="form-control" required>
                                <span v-if="errors.meta" v-text="errors.meta[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.hitos }">
                                <label for="hitos">{{ $t('backend.milestones') }}</label>
                                <input v-model="form.hitos" name="hitos" type="text" class="form-control"
                                    required>
                                <span v-if="errors.hitos" v-text="errors.hitos[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.dia_hora_reunion }">
                                <label for="dia_hora_reunion">{{ $t('backend.meeting_day_and_time') }}</label>
                                <input v-model="form.dia_hora_reunion" name="dia_hora_reunion" type="text" class="form-control"
                                    required>
                                <span v-if="errors.dia_hora_reunion" v-text="errors.dia_hora_reunion[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.periodicidad_reunion }">
                                <label for="periodicidad_reunion">{{ $t('backend.meeting_frequency') }}</label>
                                <input v-model="form.periodicidad_reunion" name="periodicidad_reunion" type="text" class="form-control" required>
                                <span v-if="errors.periodicidad_reunion" v-text="errors.periodicidad_reunion[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.impacto }">
                                <label for="impacto">{{ $t('backend.impact') }}</label>
                                <input v-model="form.impacto" name="impacto" type="text" class="form-control"
                                    required>
                                <span v-if="errors.impacto" v-text="errors.impacto[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.capacidades }">
                                <label for="capacidades">{{ $t('backend.skills') }}</label>
                                <input v-model="form.capacidades" name="capacidades" type="text" class="form-control" required>
                                <span v-if="errors.capacidades" v-text="errors.capacidades[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                </div>

                

                <div class="modal-footer text-center">
                    <div v-if="guardado" class="row  m-2">
                        <p class="text-center bg-success">
                            {{ $t('backend.changes_saved') }}
                        </p>
                    </div>
                    <button ref="cancelar" class="btn" @click="cancelar()">{{ $t('backend.cancel') }}</button>
                    <button ref="eliminar" v-show="editando" class="btn btn-danger"
                        @click.prevent="confirmar()">{{ $t('backend.eliminate') }}</button>
                    <button ref="guardar" class="btn btn-primary" @click.prevent="guardar()">{{ $t('backend.save') }}</button>
                </div>

            </div>
        </div>
    </div>
</div></template>

<script>

import vSelect from 'vue-select';
import Simplert from 'vue2-simplert';
import { debounce } from 'lodash';

export default {
    components: { 'v-select': vSelect },
    props: ['idEquipo'],
    data: function () {
        return {
            display: false,
            persona: null,
            archivo_carta_compromiso: null,
            nombre_carta_compromiso: '',
            archivo_plan_de_trabajo: null,
            personas: [],
            form: {
                idEquipo: this.idEquipo,
                idIntegrante: null,
                idPersona: null,
                rol: null,
                despliegue: null,
                relacion: null,
                estado: 1,
                fechaInicio: null,
                fechaFin: null,
                archivo_carta_compromiso: '',
                descripcion_rol : null,
                meta : null,
                hitos : null,
                dia_hora_reunion : null,
                periodicidad_reunion : null,
                impacto : null,
                capacidades : null,
            },
            errors: {},
            guardado: false
        }
    },
    mounted() {
        Event.$on('integrante:crear', this.show);
        Event.$on('integrante:editar', this.editar);
        this.guardado = false;
    },
    watch: {
        persona(v, vv) {
            if (v) this.form.idPersona = v.idPersona
        }
    },
    computed: {
        editando() {
            if (this.form['idIntegrante'])
                return true
            return false
        }
    },
    methods: {
        guardar() {
            if (this.form['idIntegrante'])
                this.update();
            else
                this.store();
        },
        store() {
            axios.post('/admin/ajax/equipos/' + this.idEquipo + '/integrante/crear', this.form)
                .then((datos) => {
                    Event.$emit('integrante:refrescar');
                    // location.reload();
                    this.submitFiles(datos.data.idIntegrante);
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });

        },

        update() {
            axios.put('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + this.form.idIntegrante, this.form)
                .then((datos) => {
                    Event.$emit('integrante:refrescar');
                    this.submitFiles();
                    this.guardado = true;
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },

        selectCarta: function () {
            this.$refs.archivo_carta_compromiso.click();
        },

        guardarCartaCompromiso: function () {
            this.archivo_carta_compromiso = this.$refs.archivo_carta_compromiso.files[0];
            this.nombre_carta_compromiso = this.$refs.archivo_carta_compromiso.files[0].name;
        },
        submitFiles() {
            const formData = new FormData();
            formData.append('archivo_carta_compromiso', this.archivo_carta_compromiso);
            formData.append('archivo_plan_de_trabajo', this.archivo_plan_de_trabajo);
            const headers = { 'Content-Type': 'multipart/form-data' };
            axios.post('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + this.form['idIntegrante'] + '/archivos', formData, { headers }).then(response => {
                this.form.archivo_carta_compromiso = response.data.archivo_carta_compromiso;
             //   console.log(response);
                this.archivo_carta_compromiso = null;
                this.nombre_carta_compromiso = '';

            }).catch((error) => {
            });
        },
        eliminar() {
            axios.delete('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + this.form.idIntegrante, this.form)
                .then((datos) => {
                    Event.$emit('integrantes:refrescar');
                    location.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            this.show();
            axios.get('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + p.idIntegrante)
                .then((datos) => {
                    this.form = datos.data;
                    this.form.fechaInicio = moment(this.form.fechaInicio).format('YYYY-MM-DD');
                    if (this.form.fechaFin)
                        this.form.fechaFin = moment(this.form.fechaFin).format('YYYY-MM-DD');
                    this.persona = datos.data.persona;
                    this.persona.nombre = datos.data.personaData.nombre;
                }).catch((error) => { debugger; });
        },
        show: function () {
            $('#inscribir-modal').modal('show'); //sino pasan cosas raras con el scroll
        },
        hide: function () {
            $('#inscribir-modal').modal('hide');
        },
        reset: function () {
            for (let field in this.form) {
                this.form[field] = null;
            }
            this.persona = null;
            this.reset_errors();
        },
        reset_errors: function () {
            for (let field in this.errors) {
                this.errors[field] = null;
                delete this.errors[field];
            }
        },
        cancelar() {
            this.reset();
            this.reset_errors();
            this.hide();
        },
        confirmar() {
            this.$refs.confirmar.openSimplert({
                title: 'Eliminar Registro',
                message: "Estás por eliminar este registro, se borrará permanentemente y no podrá recuperarse. ¿Deseas continuar?",
                useConfirmBtn: true,
                isShown: true,
                disableOverlayClick: true,
                customClass: 'confirmar',
                customCloseBtnText: 'CANCELAR',
                customCloseBtnClass: 'btn btn-default',
                customConfirmBtnText: 'Si, borrar',
                customConfirmBtnClass: 'btn btn-danger',
                onConfirm: function () {
                    this.$parent.eliminar();
                }
            })
        },
        onSearch: _.debounce(function (text, loading) {
            if (text.length > 3) {
                loading(true);
                axios.get('/ajax/coordinadores?coordinador=' + text)
                    .then((datos) => {
                        this.personas = datos.data.data;
                        loading(false);
                    })
                    .catch((error) => { loading(false); });
            }
        }, 400),

    }
}
</script>