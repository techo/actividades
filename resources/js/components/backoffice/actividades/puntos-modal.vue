<template>
    <div :class="{ 'modal':true, 'fade': true }" :style="{}" id="inscribir-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()" >
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.point') }}</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idPuntoEncuentro" class="callout callout-danger">
                        <p v-text="errors.idPuntoEncuentro[0]" ></p>
                    </div>

                    <div class="row">

                        <div class="col-md-7">
                            <div :class="{ 'form-group': true, 'has-error': errors.punto }">
                                <label for="punto" >{{ $t('backend.point') }}</label>
                                <input v-model="form.punto" name="punto" type="text" class="form-control" required >
                                <span v-if="errors.punto" v-text="errors.punto[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div :class="{ 'form-group': true, 'has-error': errors.horario }">
                                <label for="horario" >{{ $t('backend.schedule') }}</label>
                                <input v-model="form.horario" name="horario" type="time" class="form-control" required >
                                <span v-if="errors.horario" v-text="errors.horario[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.idProvincia }">
                                <label for="idProvincia">{{ $t('backend.province') }}</label>
                                <select v-model="form.idProvincia" name="idProvincia" class="form-control" required @change="getLocalidades($event);actividad.idLocalidad=null;" >>
                                    <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                                </select>
                                <span v-if="errors.idProvincia" v-text="errors.idProvincia[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.idLocalidad }">
                                <label for="idLocalidad">{{ $t('backend.location') }}</label>
                                <select v-model="form.idLocalidad" name="idLocalidad" class="form-control" required >
                                    <option v-text="localidad.localidad" v-bind:value="localidad.id" v-for="localidad in localidades" ></option>
                                </select>
                                <span v-if="errors.idLocalidad" v-text="errors.idLocalidad[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idPersona }">
                                <label for="idPersona">{{ $t('backend.person') }}</label>
                                <v-select
                                    :options="personas" 
                                    @search="onSearch" 
                                    label="nombre" 
                                    v-model="persona" 
                                    :filterable="false"
                                    :selectOnTab="true"
                                ></v-select>
                                <span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.estado }">
                                <label for="estado">{{ $t('backend.state') }}</label>
                                <select v-model="form.estado" name="estado" class="form-control" required >
                                    <option value="1" :selected="form.estado == 1 " >{{ $t('backend.active') }}</option>
                                    <option value="0" :selected="form.estado == 0 " >{{ $t('backend.inactive') }}</option>
                                </select>
                                <span v-if="errors.estado" v-text="errors.estado[0]" class="help-block" ></span>
                            </div>
                        </div>
                </div>

                </div>
                <div class="modal-footer">
                    <button ref="cancelar" class="btn" @click="cancelar()">{{ $t('backend.cancel') }}</button>
                    <button ref="eliminar" v-show="editando" class="btn btn-danger" @click.prevent="confirmar()" >{{ $t('backend.eliminate') }}</button>
                    <button ref="guardar" class="btn btn-primary" @click.prevent="guardar()" >{{ $t('backend.save') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import vSelect from 'vue-select';
import Simplert from 'vue2-simplert';
import { debounce } from 'lodash';

export default {
    components: { 'v-select': vSelect },
    props: [ 'id' ],
    data: function () {
        return {
            display: false,
            actividad: {},
            persona: null,
            personas: [],
            provincias: [],
            localidades: [],
            form: {
                punto: null,
                horario: null,
                idProvincia: null,
                idLocalidad: null,
                idPersona: null,
                estado: 1,
            },
            errors: {}
        }
    },
    mounted() {
        Event.$on('puntos:crear', this.show);
        Event.$on('puntos:editar', this.editar);
        this.getActividad();
    },
    watch: { 
        persona(v,vv) {
            if(v) this.form.idPersona = v.idPersona
        }
    },
    computed: {
        editando() {
            if(this.form['idPuntoEncuentro'])
                return true
            return false
        }
    },
    methods: {
        guardar() {
            if(this.form['idPuntoEncuentro'])
                this.update();
            else
                this.store();
        },
        store(){
            axios.post('/admin/ajax/actividades/' + this.id + '/puntos', this.form)
                .then((datos) => { 
                    Event.$emit('puntos:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        update () {
            axios.post('/admin/ajax/actividades/' + this.id + '/puntos/' + this.form.idPuntoEncuentro, this.form)
                .then((datos) => { 
                    Event.$emit('puntos:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        eliminar () {
            axios.delete('/admin/ajax/actividades/' + this.id + '/puntos/' + this.form.idPuntoEncuentro, this.form)
                .then((datos) => { 
                    Event.$emit('puntos:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            this.show();
            axios.get('/admin/ajax/actividades/' + this.id + '/puntos/' + p.id)
                .then((datos) => { 
                    this.form = datos.data; 
                    this.getLocalidades();
                    this.persona = datos.data.persona;
                }).catch((error) => { debugger; });
        },
        getActividad() {
            axios.get('/admin/ajax/actividades/' + this.id )
                .then((datos) => { 
                    this.actividad = datos.data;
                    this.getProvincias();
                }).catch((error) => { debugger; });
        },
        getProvincias(){
            axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias')
                .then((datos) => { this.provincias = datos.data; }).catch((error) => { debugger; });
        },
        getLocalidades(){
            axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias/' + this.form.idProvincia + '/localidades')
                    .then((datos) => { this.localidades = datos.data; }).catch((error) => { debugger; });
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
        confirmar () {
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
        onSearch: _.debounce( function (text, loading) {
            if(text.length > 3) {
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