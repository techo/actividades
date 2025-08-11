<template>
    <div :class="{ 'modal': true, 'fade': true }" :style="{}" id="red-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.new_red') }}</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idRedComunidad" class="callout callout-danger">
                        <p v-text="errors.idRedComunidad[0]"></p>
                    </div>

                     <!-- Nombre de la organización -->
                    <div class="row">
                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.nombre }">
                            <label for="nombre">{{ $t('backend.nombre_organizacion') }}</label>
                            <input v-model="form.nombre" type="text" name="nombre" class="form-control" required>
                            <span v-if="errors.nombre" v-text="errors.nombre[0]" class="help-block"></span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.tipo }">
                            <label for="tipo">{{ $t('backend.tipo') }}</label>
                            <select v-model="form.tipo" name="tipo" class="form-control" required>
                                <option value="entidad_estatal_municipal">{{ $t('backend.entidad_estatal_municipal') }}</option>
                                <option value="organismo_internacional">{{ $t('backend.organismo_internacional') }}</option>
                                <option value="organizacion_local">{{ $t('backend.organizacion_local') }}</option>
                                <option value="organizacion_base_comunitaria">{{ $t('backend.organizacion_base_comunitaria') }}</option>
                                <option value="empresa_servicios_publicos">{{ $t('backend.empresa_servicios_publicos') }}</option>
                                <option value="empresa_privada">{{ $t('backend.empresa_privada') }}</option>
                            </select>
                            <span v-if="errors.tipo" v-text="errors.tipo[0]" class="help-block"></span>
                        </div>
                    </div>

                   

                    <!-- Relación con la comunidad -->
                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.relacion }">
                            <label for="relacion">{{ $t('backend.relacion') }}</label>
                            <select v-model="form.relacion" name="relacion" class="form-control" required>
                                <option value="excelente">{{ $t('backend.excelente') }}</option>
                                <option value="buena">{{ $t('backend.buena') }}</option>
                                <option value="regular">{{ $t('backend.regular') }}</option>
                                <option value="mala">{{ $t('backend.mala') }}</option>
                            </select>
                            <span v-if="errors.relacion" v-text="errors.relacion[0]" class="help-block"></span>
                        </div>
                    </div>

                    <!-- Presencia en la comunidad -->
                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.presencia }">
                            <label for="presencia">{{ $t('backend.presencia') }}</label>
                            <select v-model="form.presencia" name="presencia" class="form-control" required>
                                <option value="constante">{{ $t('backend.constante') }}</option>
                                <option value="intermitente">{{ $t('backend.intermitente') }}</option>
                                <option value="poca">{{ $t('backend.poca') }}</option>
                            </select>
                            <span v-if="errors.presencia" v-text="errors.presencia[0]" class="help-block"></span>
                        </div>
                    </div>

                    <!-- Nombre de contacto -->
                    <div class="col-md-4">
                        <div :class="{ 'form-group': true, 'has-error': errors.nombre_contacto }">
                            <label for="nombre_contacto">{{ $t('backend.nombre_contacto') }}</label>
                            <input v-model="form.nombre_contacto" type="text" name="nombre_contacto" class="form-control">
                            <span v-if="errors.nombre_contacto" v-text="errors.nombre_contacto[0]" class="help-block"></span>
                        </div>
                    </div>

                    <!-- Teléfono de contacto -->
                    <div class="col-md-4">
                        <div :class="{ 'form-group': true, 'has-error': errors.telefono_contacto }">
                            <label for="telefono_contacto">{{ $t('backend.telefono_contacto') }}</label>
                            <input v-model="form.telefono_contacto" type="text" name="telefono_contacto" class="form-control">
                            <span v-if="errors.telefono_contacto" v-text="errors.telefono_contacto[0]" class="help-block"></span>
                        </div>
                    </div>

                    <!-- Mail de contacto -->
                    <div class="col-md-4">
                        <div :class="{ 'form-group': true, 'has-error': errors.mail_contacto }">
                            <label for="mail_contacto">{{ $t('backend.mail_contacto') }}</label>
                            <input v-model="form.mail_contacto" type="email" name="mail_contacto" class="form-control">
                            <span v-if="errors.mail_contacto" v-text="errors.mail_contacto[0]" class="help-block"></span>
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
                idRedComunidad: null,
                tipo: null, 
                nombre: '',
                presencia: null,
                nombre_contacto: '',
                telefono_contacto: '',
                mail_contacto: '',
                comentarios: '',
            },
            errors: {},
            guardado: false
        }
    },
    mounted() {
        Event.$on('red:crear', this.show);
        Event.$on('red:editar', this.editar);
        this.guardado = false;
    },
    watch: {
        persona(v, vv) {
            if (v) this.form.idPersona = v.idPersona
        }
    },
    computed: {
        editando() {
            if (this.form['idRedComunidad'])
                return true
            return false
        },
    },
    methods: {
        guardar() {
            if (this.form['idRedComunidad'])
                this.update();
            else
                this.store();
        },
        store() {
            axios.post('/admin/ajax/comunidades/' + this.idComunidad + '/red/crear', this.form)
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
            axios.put('/admin/ajax/comunidades/' + this.idComunidad + '/red/' + this.form.idRedComunidad, this.form)
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
            axios.delete('/admin/ajax/comunidades/' + this.idComunidad + '/red/' + this.form.idRedComunidad, this.form)
                .then((datos) => {
                    this.$emit('actualizar');
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            axios.get('/admin/ajax/comunidades/' + this.idComunidad + '/red/' + p.idRedComunidad)
                .then((datos) => {
                    this.form = datos.data;
                }).catch((error) => { debugger; });
            this.show();
        },
        show: function () {
            $('#red-modal').modal('show'); //sino pasan cosas raras con el scroll
        },
        hide: function () {
            $('#red-modal').modal('hide');
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