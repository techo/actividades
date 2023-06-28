<template>
    <div :class="{ 'modal': true, 'fade': true }" :style="{}" id="inscribir-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Nuevo Integrante</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idIntegrante" class="callout callout-danger">
                        <p v-text="errors.idIntegrante[0]"></p>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idPersona }">
                                <label for="idPersona">Persona</label>
                                <v-select :options="personas" @search="onSearch" label="nombre" v-model="persona"
                                    :filterable="false" :selectOnTab="true"></v-select>
                                <span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                                <label for="rol">Rol</label>
                                <input v-model="form.rol" name="rol" type="text" class="form-control" required>
                                <span v-if="errors.rol" v-text="errors.rol[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.estado }">
                                <label for="estado">Estado</label>
                                <select v-model="form.estado" name="estado" class="form-control" required>
                                    <option value="1" :selected="form.estado == 1">Activo</option>
                                    <option value="0" :selected="form.estado == 0">Inactivo</option>
                                </select>
                                <span v-if="errors.estado" v-text="errors.estado[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.fechaInicio }">
                                <label for="fechaInicio">fechaInicio</label>
                                <input v-model="form.fechaInicio" name="fechaInicio" type="date" class="form-control"
                                    required>
                                <span v-if="errors.fechaInicio" v-text="errors.fechaInicio[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6" v-if="form.estado==0">
                            <div :class="{ 'form-group': true, 'has-error': errors.fechaFin }">
                                <label for="fechaFin">fechaFin</label>
                                <input v-model="form.fechaFin" name="fechaFin" type="date" class="form-control" required>
                                <span v-if="errors.fechaFin" v-text="errors.fechaFin[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="modal-footer">
                <button ref="cancelar" class="btn" @click="cancelar()">Cancelar</button>
                <button ref="eliminar" v-show="editando" class="btn btn-danger"
                    @click.prevent="confirmar()">Eliminar</button>
                <button ref="guardar" class="btn btn-primary" @click.prevent="guardar()">Guardar</button>
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
            personas: [],
            form: {
                idEquipo: this.idEquipo,
                idIntegrante: null,
                idPersona: null,
                rol: null,
                estado: 1,
                fechaInicio: null,
                fechaFin: null,
            },
            errors: {}
        }
    },
    mounted() {
        Event.$on('integrante:crear', this.show);
        Event.$on('integrante:editar', this.editar);
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
                    location.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        update() {
            axios.put('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + this.form.idIntegrante, this.form)
                .then((datos) => {
                    Event.$emit('integrante:refrescar');
                    location.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
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
            console.log("holas");
            this.show();
            axios.get('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + p.idIntegrante)
                .then((datos) => {
                    this.form = datos.data;
                    this.form.fechaInicio = moment(this.form.fechaInicio).format('YYYY-MM-DD');
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