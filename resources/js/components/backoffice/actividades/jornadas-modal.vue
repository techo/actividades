<template>
    <div :class="{ 'modal':true, 'fade': true }" :style="{}" id="jornadas-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()" >
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.jornada') }}</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idJornada" class="callout callout-danger">
                        <p v-text="errors.idJornada[0]" ></p>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.nombre }">
                                <label for="nombre" >{{ $t('backend.name') }}</label>
                                <input v-model="form.nombre" name="nombre" type="text" class="form-control" required >
                                <span v-if="errors.nombre" v-text="errors.nombre[0]" class="help-block" ></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="fechaInicio" >{{ $t('backend.start_date') }}</label>
                            <div :class="{ 'input-group': true, 'has-error': errors.fechaInicio }" >
                                <input v-model="fechas.fechaInicio" type="date" @change="fechas.fechaFin=fechas.fechaInicio;" class="form-control" required style="line-height: inherit;">
                                <span class="help-block">{{ errors.fechaInicio }}</span>
                                <span class="input-group-addon">
                                    <input v-model="horas.fechaInicio" type="time" required style="border: none; height: 20px;">
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="horario" >{{ $t('backend.end_date') }}</label>
                            <div :class="{ 'input-group': true, 'has-error': errors.fechaFin }" >
                                <input v-model="fechas.fechaFin" type="date" @change="fechas.fechaFin=fechas.fechaFin;" class="form-control" required style="line-height: inherit;">
                                <span class="help-block">{{ errors.fechaFin }}</span>
                                <span class="input-group-addon">
                                    <input v-model="horas.fechaFin" type="time" required style="border: none; height: 20px;">
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
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
                    </div>
                    <div class="row">
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
    props: [ 'actividad' ],
    data: function () {
        return {
            display: false,
            persona: null,
            personas: [],
            fechas:  {
                    fechaInicio: null,
                    fechaFin: null,
                },
            horas: {
                    fechaInicio: "19:00:00",
                    fechaFin: "21:00:00",
            },
            form: {
                nombre: null,
                fechaInicio: null,
                fechaFin: null,
                activo: 1,
                idPersona: null,
                idActividad: null,
            },
            errors: {}
        }
    },
    created() {
        this.fechas.fechaInicio = moment(this.actividad.fechaInicio).format('YYYY-MM-DD');
        this.horas.fechaInicio = moment(this.actividad.fechaInicio).format('HH:mm:ss');
        this.fechas.fechaFin = moment(this.actividad.fechaFin).format('YYYY-MM-DD');
        this.horas.fechaFin = moment(this.actividad.fechaFin).format('HH:mm:ss');
        this.form.idActividad = this.actividad.idActividad;
    },
    mounted() {
        Event.$on('jornadas:crear', this.show);
        Event.$on('jornadas:editar', this.editar);
    },
    watch: { 
        persona(v,vv) {
            if(v) this.form.idPersona = v.idPersona
        }
    },
    computed: {
        editando() {
            if(this.form['idJornada'])
                return true
            return false
        }
    },
    methods: {
        guardar() {
            if(this.form['idJornada'])
                this.update();
            else
                this.store();
        },
        store(){
            this.form.fechaInicio = moment(this.fechas.fechaInicio + ' ' + this.horas.fechaInicio).format('YYYY-MM-DD HH:mm:ss');
                this.form.fechaFin = moment(this.fechas.fechaFin + ' ' + this.horas.fechaFin).format('YYYY-MM-DD HH:mm:ss');
              
            axios.post('/admin/ajax/actividades/' + this.actividad.idActividad + '/jornadas', this.form)
                .then((datos) => { 
                    Event.$emit('jornadas:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        update () {
            axios.put('/admin/ajax/actividades/' + this.actividad.idActividad + '/jornadas/' + this.form.idJornada, this.form)
                .then((datos) => { 
                    Event.$emit('jornadas:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        eliminar () {
            axios.delete('/admin/ajax/actividades/' + this.id + '/jornadas/' + this.form.idJornada, this.form)
                .then((datos) => { 
                    Event.$emit('jornadas:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            this.show();
            this.form = p; 
            // this.fechas.fechaInicio = moment(p.fechaInicio).format('YYYY-MM-DD');
            // this.horas.fechaInicio = moment(p.fechaInicio).format('HH:mm:ss');
            // this.fechas.fechaFin = moment(p.fechaFin).format('YYYY-MM-DD');
            // this.horas.fechaFin = moment(p.fechaFin).format('HH:mm:ss');

            this.persona = datos.data.persona
        },
        show: function () {
            $('#jornadas-modal').modal('show'); //sino pasan cosas raras con el scroll
        },
        hide: function () { 
            $('#jornadas-modal').modal('hide');
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