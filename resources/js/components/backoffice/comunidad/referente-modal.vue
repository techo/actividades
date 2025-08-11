<template>
    <div :class="{ 'modal': true, 'fade': true }" :style="{}" id="referente-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.new_referente') }}</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idReferenteComunidad" class="callout callout-danger">
                        <p v-text="errors.idReferenteComunidad[0]"></p>
                    </div>

                     <!-- Nombre de la referente -->
                    <div class="row">
                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.nombre }">
                            <label for="nombre">{{ $t('backend.nombre') }}</label>
                            <input v-model="form.nombre" type="text" name="nombre" class="form-control" required>
                            <span v-if="errors.nombre" v-text="errors.nombre[0]" class="help-block"></span>
                        </div>
                    </div>

                     <!-- Rol-->

                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                            <label for="rol">{{ $t('frontend.rol') }}</label>
                            <input v-model="form.rol" type="text" name="rol" class="form-control" required>
                            <span v-if="errors.rol" v-text="errors.rol[0]" class="help-block"></span>
                        </div>
                    </div>
                    
                     <!-- telefono-->

                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                            <label for="telefono">{{ $t('backend.telefono') }}</label>
                            <input v-model="form.telefono" type="text" name="telefono" class="form-control" required>
                            <span v-if="errors.telefono" v-text="errors.telefono[0]" class="help-block"></span>
                        </div>
                    </div>

                     <!-- mail-->

                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                            <label for="mail">{{ $t('frontend.mail') }}</label>
                            <input v-model="form.mail" type="text" name="mail" class="form-control" required>
                            <span v-if="errors.mail" v-text="errors.mail[0]" class="help-block"></span>
                        </div>
                    </div>

                    <!-- Comentarios -->
                    <div class="col-md-12">
                        <div :class="{ 'form-group': true, 'has-error': errors.comentarios }">
                            <label for="comentarios">{{ $t('backend.comentarios') }}</label>
                            <textarea v-model="form.comentarios" name="comentarios" class="form-control" rows="3"></textarea>
                            <span v-if="errors.comentarios" v-text="errors.comentarios[0]" class="help-block"></span>
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
                </div>

                

                <div class="modal-footer text-center">
                    <div v-if="guardado" class="row">
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

export default {
    components: { 'v-select': vSelect},
    props: ['idComunidad'],
    data: function () {
        return {
            display: false,
            form: {
                idComunidad: this.idComunidad,
                idReferenteComunidad: null,
                nombre: '',
                rol: '',
                telefono: '',
                mail: '',
                estado: 1,
                comentarios: '',
            },
            errors: {},
            guardado: false
        }
    },
    mounted() {
        Event.$on('referente:crear', this.show);
        Event.$on('referente:editar', this.editar);
        this.guardado = false;
    },
    watch: {
        persona(v, vv) {
            if (v) this.form.idPersona = v.idPersona
        }
    },
    computed: {
        editando() {
            if (this.form['idReferenteComunidad'])
                return true
            return false
        },
    },
    methods: {
        guardar() {
            if (this.form['idReferenteComunidad'])
                this.update();
            else
                this.store();
        },
        store() {
            axios.post('/admin/ajax/comunidades/' + this.idComunidad + '/referentes/crear', this.form)
                .then((datos) => {
                    this.guardado = true;
                    setTimeout(() => {
                        this.guardado = false;
                        this.$emit('actualizar');
                        this.cancelar();
                    }, 2000);
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });

        },

        update() {
            axios.put('/admin/ajax/comunidades/' + this.idComunidad + '/referentes/' + this.form.idReferenteComunidad, this.form)
                .then((datos) => {
                    setTimeout(() => {
                        this.guardado = false;
                        this.$emit('actualizar');
                        this.cancelar();
                    }, 2000);
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },

        eliminar() {
            axios.delete('/admin/ajax/comunidades/' + this.idComunidad + '/referentes/' + this.form.idReferenteComunidad, this.form)
                .then((datos) => {
                    this.$emit('actualizar');
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            axios.get('/admin/ajax/comunidades/' + this.idComunidad + '/referentes/' + p.idReferenteComunidad)
                .then((datos) => {
                    this.form = datos.data;
                }).catch((error) => { debugger; });
            this.show();
        },
        show: function () {
            $('#referente-modal').modal('show'); //sino pasan cosas raras con el scroll
        },
        hide: function () {
            $('#referente-modal').modal('hide');
        },
        reset: function () {
            for (let field in this.form) {
                this.form[field] = null;
            }
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
    }
}
</script>